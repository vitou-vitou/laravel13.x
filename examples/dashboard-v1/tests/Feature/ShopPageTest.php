<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_shop(): void
    {
        $this->get(route('shop.index'))->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_active_products(): void
    {
        $user = $this->adminUser();

        Product::factory()->create([
            'name' => ['en' => 'Visible Product', 'es' => 'Producto visible'],
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => ['en' => 'Hidden Product', 'es' => 'Producto oculto'],
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Visible Product')
            ->assertDontSee('Hidden Product')
            ->assertSee('Add to cart');
    }

    public function test_shop_can_filter_by_category(): void
    {
        $user = $this->adminUser();

        $electronics = Category::factory()->create([
            'name' => ['en' => 'Electronics', 'es' => 'Electrónica'],
            'slug' => 'electronics',
        ]);

        $apparel = Category::factory()->create([
            'name' => ['en' => 'Apparel', 'es' => 'Ropa'],
            'slug' => 'apparel',
        ]);

        Product::factory()->create([
            'category_id' => $electronics->id,
            'name' => ['en' => 'Headphones', 'es' => 'Auriculares'],
        ]);

        Product::factory()->create([
            'category_id' => $apparel->id,
            'name' => ['en' => 'Hoodie', 'es' => 'Sudadera'],
        ]);

        $this->actingAs($user)
            ->get(route('shop.index', ['category' => 'electronics']))
            ->assertOk()
            ->assertSee('Headphones')
            ->assertDontSee('Hoodie');
    }

    public function test_user_can_add_product_to_cart_from_shop(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('customer');

        $product = Product::factory()->create([
            'name' => ['en' => 'Shop Widget', 'es' => 'Widget'],
            'price_cents' => 1_500,
        ]);

        $this->actingAs($user)
            ->post(route('shop.cart.add', $product), ['quantity' => 2])
            ->assertRedirect(route('shop.index'))
            ->assertSessionHas('cart_added');

        $this->assertSame(3_000, (new CartService)->totalCents($user));
    }

    public function test_cannot_add_inactive_product_to_cart(): void
    {
        $user = $this->adminUser();
        $product = Product::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->post(route('shop.cart.add', $product))
            ->assertNotFound();

        $this->assertSame(0, (new CartService)->totalCents($user));
    }
}
