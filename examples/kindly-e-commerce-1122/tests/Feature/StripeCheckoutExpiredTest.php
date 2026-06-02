<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SendsStripeWebhooks;
use Tests\TestCase;

class StripeCheckoutExpiredTest extends TestCase
{
    use RefreshDatabase;
    use SendsStripeWebhooks;

    public function test_expired_checkout_session_restores_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 3]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_expired',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price_cents' => 500,
        ]);
        $product->decrement('stock_quantity', 2);
        $product->refresh();
        $this->assertSame(1, $product->stock_quantity);

        $this->postStripeWebhook([
            'id' => 'evt_test_expired',
            'object' => 'event',
            'type' => 'checkout.session.expired',
            'data' => [
                'object' => [
                    'id' => 'cs_test_expired',
                    'object' => 'checkout.session',
                    'metadata' => [
                        'order_id' => (string) $order->id,
                    ],
                ],
            ],
        ])->assertOk();

        $product->refresh();
        $this->assertSame(3, $product->stock_quantity);
        $this->assertTrue($order->fresh()->isPending());
    }
}
