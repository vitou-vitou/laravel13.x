<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_product_with_variant(): void
    {
        $vendor = Vendor::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($vendor->user)
            ->post(route('vendor.products.store'), [
                'name' => 'Handwoven Scarf',
                'description' => 'Soft cotton scarf from Battambang.',
                'category_id' => $category->id,
                'status' => 'active',
                'variants' => [
                    ['name' => 'Default', 'price_cents' => 4500, 'stock_qty' => 12, 'sku' => 'SCARF-1'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendor->id,
            'name' => 'Handwoven Scarf',
            'status' => ProductStatus::Active->value,
        ]);

        $product = Product::query()->withoutGlobalScopes()->where('name', 'Handwoven Scarf')->first();
        $this->assertSame(4500, $product->variants->first()->price_cents);
    }

    public function test_vendor_can_update_own_product_stock(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_qty' => 10,
        ]);

        $this->actingAs($vendor->user)
            ->patch(route('vendor.products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'category_id' => $product->category_id,
                'status' => 'active',
                'variants' => [
                    [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price_cents' => $variant->price_cents,
                        'stock_qty' => 2,
                        'sku' => $variant->sku,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(2, $variant->fresh()->stock_qty);
    }

    public function test_vendor_cannot_edit_another_vendors_product(): void
    {
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendorB->id]);

        $this->actingAs($vendorA->user)
            ->get(route('vendor.products.edit', $product))
            ->assertForbidden();
    }

    public function test_vendor_order_show_includes_shipping_snapshot(): void
    {
        $vendor = Vendor::factory()->create();
        $customer = User::factory()->create();
        $address = ShippingAddress::factory()->create([
            'user_id' => $customer->id,
            'line1' => '12 Monivong Blvd',
        ]);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'shipping_address_snapshot' => $address->toSnapshot(),
        ]);

        $group = OrderGroup::factory()->create([
            'vendor_id' => $vendor->id,
            'order_id' => $order->id,
        ]);

        $this->actingAs($vendor->user)
            ->get(route('vendor.orders.show', $group))
            ->assertOk()
            ->assertSee('12 Monivong Blvd')
            ->assertSee($customer->email);
    }

    public function test_dashboard_shows_low_stock_warning(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => ProductStatus::Active,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_qty' => 2,
            'name' => 'Small',
        ]);

        $this->actingAs($vendor->user)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Low stock alert')
            ->assertSee('Small');
    }

    public function test_draft_product_not_visible_in_catalog(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->draft()->create(['vendor_id' => $vendor->id]);

        $this->get(route('catalog.show', $product->id))->assertNotFound();
    }
}
