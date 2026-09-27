<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
    ) {}

    public function placeFromCart(): Order
    {
        if ($this->cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $lines = $this->cart->lines();

        foreach ($lines as $line) {
            if ($line['quantity'] > $line['product']->stock_quantity) {
                throw ValidationException::withMessages([
                    'cart' => "Not enough stock for {$line['product']->name}.",
                ]);
            }
        }

        $order = DB::transaction(function () use ($lines) {
            $subtotalCents = $lines->sum('line_total_cents');
            $discountCents = $this->coupons->discountCents($subtotalCents);
            $totalCents = max(0, $subtotalCents - $discountCents);
            $coupon = $this->coupons->resolveForSubtotal($subtotalCents);

            $order = Order::query()->create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'subtotal_cents' => $subtotalCents,
                'discount_cents' => $discountCents,
                'coupon_code' => $coupon?->code,
                'total_cents' => $totalCents,
            ]);

            $sorted = $lines->sortBy(fn (array $line) => $line['product']->id)->values();

            foreach ($sorted as $line) {
                $product = Product::query()
                    ->whereKey($line['product']->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($line['quantity'] > $product->stock_quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Not enough stock for {$product->name}.",
                    ]);
                }

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price_cents' => $product->price_cents,
                ]);

                $product->decrement('stock_quantity', $line['quantity']);
            }

            return $order;
        });

        $this->cart->clear();
        $this->coupons->clear();

        return $order;
    }
}
