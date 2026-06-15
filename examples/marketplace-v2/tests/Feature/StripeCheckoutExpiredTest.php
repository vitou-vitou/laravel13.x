<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SendsStripeWebhooks;
use Tests\TestCase;

class StripeCheckoutExpiredTest extends TestCase
{
    use RefreshDatabase;
    use SendsStripeWebhooks;

    public function test_expired_checkout_session_restores_stock(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_qty' => 3,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PendingPayment,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_expired',
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::Pending,
            'amount_cents' => 1000,
        ]);
        $group = OrderGroup::factory()->create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'subtotal_cents' => 1000,
        ]);
        OrderLine::factory()->create([
            'order_group_id' => $group->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price_cents' => 500,
        ]);
        $variant->decrement('stock_qty', 2);
        $variant->refresh();
        $this->assertSame(1, $variant->stock_qty);

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

        $variant->refresh();
        $this->assertSame(3, $variant->stock_qty);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }
}
