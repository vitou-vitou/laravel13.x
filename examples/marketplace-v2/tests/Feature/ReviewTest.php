<?php

namespace Tests\Feature;

use App\Enums\OrderGroupStatus;
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
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_purchased_product(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
        ]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::Completed,
            'amount_cents' => 1000,
        ]);
        $group = OrderGroup::factory()->create([
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'status' => OrderGroupStatus::Delivered,
            'subtotal_cents' => 1000,
        ]);
        OrderLine::factory()->create([
            'order_group_id' => $group->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price_cents' => 1000,
            'product_name_snapshot' => $product->name,
        ]);

        $review = app(ReviewService::class)->create($customer, $product, $order, 5, 'Great product');

        $this->assertSame(5, $review->rating);
        $this->assertSame(5.0, (float) $vendor->fresh()->rating_avg);
    }
}
