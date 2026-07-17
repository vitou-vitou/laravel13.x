<?php

namespace App\Services;

use App\Events\NewOrderCreated;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private ?OrderMailNotifier $orderMailNotifier = null,
    ) {
        $this->orderMailNotifier ??= new OrderMailNotifier;
    }

    public function checkout(User $user, string $method = 'card'): Order
    {
        $totalCents = $this->cartService->totalCents($user);

        if ($totalCents <= 0) {
            throw new RuntimeException('Cart is empty.');
        }

        $order = DB::transaction(function () use ($user, $totalCents, $method) {
            $customer = Customer::query()->firstOrCreate(
                ['email' => $user->email],
                ['name' => $user->name],
            );

            $cart = $this->cartService->forUser($user);
            $cart->load('items.product');

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'amount_cents' => $totalCents,
                'status' => 'paid',
                'ordered_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->getTranslation('name', 'en'),
                    'quantity' => $item->quantity,
                    'unit_price_cents' => $item->product->price_cents,
                    'line_total_cents' => $item->product->price_cents * $item->quantity,
                ]);
            }

            Payment::query()->create([
                'order_id' => $order->id,
                'amount_cents' => $totalCents,
                'status' => 'completed',
                'method' => $method,
                'reference' => sprintf('CHK-%06d', $order->id),
            ]);

            $this->cartService->clear($user);

            return $order->fresh(['customer', 'payments', 'items']);
        });

        $this->orderMailNotifier->notifyAdmins($order);

        NewOrderCreated::dispatch($order);

        return $order;
    }
}
