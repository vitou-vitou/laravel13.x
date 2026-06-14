<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\OrderGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_splits_order_into_vendor_groups(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $category = Category::factory()->create();

        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();

        $variantA = ProductVariant::factory()->create([
            'product_id' => Product::factory()->create([
                'vendor_id' => $vendorA->id,
                'category_id' => $category->id,
                'status' => ProductStatus::Active,
            ])->id,
            'price_cents' => 1000,
            'stock_qty' => 5,
        ]);

        $variantB = ProductVariant::factory()->create([
            'product_id' => Product::factory()->create([
                'vendor_id' => $vendorB->id,
                'category_id' => $category->id,
                'status' => ProductStatus::Active,
            ])->id,
            'price_cents' => 2500,
            'stock_qty' => 5,
        ]);

        $this->actingAs($customer);

        $cart = app(CartService::class);
        $cart->add($variantA, 2);
        $cart->add($variantB, 1);

        $order = app(CheckoutService::class)->placeFromCart();

        $this->assertSame(4500, $order->total_cents);
        $this->assertCount(2, $order->groups);
        $this->assertSame(OrderStatus::PendingPayment, $order->status);

        $groupA = $order->groups->firstWhere('vendor_id', $vendorA->id);
        $this->assertSame(2000, $groupA->subtotal_cents);
        $this->assertSame(1000, $groupA->commission_bps);

        $line = $groupA->lines->first();
        $this->assertSame('Default', $line->variant_name_snapshot);
        $this->assertSame(1000, $line->unit_price_cents);

        $this->assertSame(3, $variantA->fresh()->stock_qty);
        $this->assertSame(4, $variantB->fresh()->stock_qty);
    }

    public function test_vendor_cannot_view_other_vendor_order_group(): void
    {
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();

        $group = OrderGroup::factory()->create(['vendor_id' => $vendorB->id]);

        $this->actingAs($vendorA->user)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertDontSee('Order #'.$group->order_id);
    }
}
