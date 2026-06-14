<?php

namespace Tests\Feature;

use App\Enums\OrderGroupStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\WishlistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_manage_shipping_addresses_and_default(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)
            ->post(route('account.addresses.store'), [
                'label' => 'Home',
                'name' => 'Jane Doe',
                'line1' => '123 Main St',
                'city' => 'Portland',
                'region' => 'OR',
                'postal_code' => '97201',
                'country' => 'US',
                'is_default' => '1',
            ])
            ->assertRedirect(route('account.addresses.index'));

        $address = ShippingAddress::query()->first();
        $this->assertTrue($address->is_default);

        $second = ShippingAddress::factory()->create([
            'user_id' => $customer->id,
            'label' => 'Office',
            'is_default' => false,
        ]);

        $this->actingAs($customer)
            ->patch(route('account.addresses.update', $second), [
                'label' => 'Office',
                'name' => $second->name,
                'line1' => $second->line1,
                'city' => $second->city,
                'region' => $second->region,
                'postal_code' => $second->postal_code,
                'country' => $second->country,
                'is_default' => '1',
            ])
            ->assertRedirect(route('account.addresses.index'));

        $this->assertFalse($address->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_checkout_stores_shipping_address_snapshot(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $address = ShippingAddress::factory()->default()->create([
            'user_id' => $customer->id,
            'line1' => '500 Oak Ave',
        ]);

        $this->actingAs($customer);
        $this->seedCartItem($customer, 3000);

        $order = app(CheckoutService::class)->placeFromCart($address->id);

        $this->assertSame('500 Oak Ave', $order->shipping_address_snapshot['line1']);
    }

    public function test_wishlist_toggle_and_add_to_cart(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $product = $this->activeProduct(2500);

        $this->actingAs($customer)
            ->post(route('wishlist.store', $product))
            ->assertRedirect();

        $this->assertTrue(app(WishlistService::class)->isWishlisted($customer, $product));

        $this->actingAs($customer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee($product->name);

        $this->actingAs($customer)
            ->post(route('wishlist.cart', $product))
            ->assertRedirect(route('cart.index'));

        $this->assertFalse(app(CartService::class)->isEmpty());
    }

    public function test_order_show_displays_timeline_with_tracking(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $this->actingAs($customer);
        $this->seedCartItem($customer, 4000);

        $order = app(CheckoutService::class)->placeFromCart();
        $group = $order->groups->first();
        $group->update([
            'status' => OrderGroupStatus::Shipped,
            'tracking_number' => 'TRACK-XYZ-99',
            'shipped_at' => now(),
        ]);

        $order->update(['status' => \App\Enums\OrderStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Shipped')
            ->assertSee('TRACK-XYZ-99');
    }

    public function test_customer_cannot_update_another_users_address(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Customer]);
        $other = User::factory()->create(['role' => UserRole::Customer]);
        $address = ShippingAddress::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->patch(route('account.addresses.update', $address), [
                'label' => 'Hacked',
                'name' => 'Bad',
                'line1' => '1 Evil Rd',
                'city' => 'X',
                'region' => 'X',
                'postal_code' => '00000',
                'country' => 'US',
            ])
            ->assertForbidden();
    }

    private function seedCartItem(User $customer, int $priceCents): ProductVariant
    {
        $category = Category::factory()->create();
        $vendor = Vendor::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => Product::factory()->create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'status' => ProductStatus::Active,
            ])->id,
            'price_cents' => $priceCents,
            'stock_qty' => 5,
        ]);

        $this->actingAs($customer);
        app(CartService::class)->add($variant, 1);

        return $variant;
    }

    private function activeProduct(int $priceCents): Product
    {
        $category = Category::factory()->create();
        $vendor = Vendor::factory()->create();

        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price_cents' => $priceCents,
            'stock_qty' => 3,
        ]);

        return $product->fresh();
    }
}
