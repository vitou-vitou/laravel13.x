<?php

namespace App\Services;

use App\Enums\OrderGroupStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CartLine;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\PaymentAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private CommissionService $commission,
        private PromoCodeService $promoCodes,
        private ShippingAddressService $shippingAddresses,
    ) {}

    public function placeFromCart(?int $shippingAddressId = null): Order
    {
        if (! auth()->check()) {
            throw ValidationException::withMessages([
                'cart' => 'You must be logged in to checkout.',
            ]);
        }

        if ($this->cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $lines = $this->cart->lines();

        foreach ($lines as $line) {
            $variant = $line->variant->fresh();

            if (! $variant->product->isActive()) {
                throw ValidationException::withMessages([
                    'cart' => "{$variant->product->name} is no longer available.",
                ]);
            }

            if (! $variant->hasStock($line->quantity)) {
                throw ValidationException::withMessages([
                    'cart' => "Not enough stock for {$variant->product->name}.",
                ]);
            }
        }

        $commissionBps = $this->commission->defaultBps();
        $subtotalCents = $lines->sum(fn (CartLine $line) => $line->lineTotalCents());
        $promo = $this->promoCodes->appliedPromo();

        if ($promo !== null) {
            $this->promoCodes->assertUsableForSubtotal($promo, $subtotalCents, $lines);
        }

        $discountCents = $promo ? $this->promoCodes->discountCents($promo, $subtotalCents, $lines) : 0;
        $totalCents = $subtotalCents - $discountCents;

        $shippingAddress = $this->shippingAddresses->resolveForCheckout(auth()->user(), $shippingAddressId);
        $shippingSnapshot = $shippingAddress?->toSnapshot();

        $order = DB::transaction(function () use ($lines, $commissionBps, $subtotalCents, $discountCents, $totalCents, $promo, $shippingSnapshot) {
            $order = Order::query()->create([
                'user_id' => auth()->id(),
                'promo_code_id' => $promo?->id,
                'status' => OrderStatus::PendingPayment,
                'subtotal_cents' => $subtotalCents,
                'discount_cents' => $discountCents,
                'total_cents' => $totalCents,
                'shipping_address_snapshot' => $shippingSnapshot,
            ]);

            $grouped = $lines->groupBy(fn (CartLine $line) => $line->variant->product->vendor_id);

            foreach ($grouped as $vendorId => $vendorLines) {
                $subtotal = $vendorLines->sum(fn (CartLine $line) => $line->lineTotalCents());

                $group = OrderGroup::query()->create([
                    'order_id' => $order->id,
                    'vendor_id' => $vendorId,
                    'status' => OrderGroupStatus::Pending,
                    'commission_bps' => $commissionBps,
                    'subtotal_cents' => $subtotal,
                ]);

                foreach ($vendorLines as $line) {
                    $variant = $line->variant->fresh();

                    if (! $variant->hasStock($line->quantity)) {
                        throw ValidationException::withMessages([
                            'cart' => "Not enough stock for {$variant->product->name}.",
                        ]);
                    }

                    OrderLine::query()->create([
                        'order_group_id' => $group->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $line->quantity,
                        'unit_price_cents' => $variant->price_cents,
                        'product_name_snapshot' => $variant->product->name,
                        'variant_name_snapshot' => $variant->name,
                    ]);

                    $variant->decrement('stock_qty', $line->quantity);
                }
            }

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'status' => PaymentStatus::Pending,
                'amount_cents' => $totalCents,
            ]);

            PaymentAuditLog::query()->create([
                'payment_id' => $payment->id,
                'from_status' => null,
                'to_status' => PaymentStatus::Pending->value,
                'note' => 'Order placed',
            ]);

            return $order->fresh(['groups.lines', 'payment']);
        });

        $this->cart->clear();
        $this->cart->cart()->update(['status' => 'checked_out']);
        $this->promoCodes->clearSession();

        return $order;
    }
}
