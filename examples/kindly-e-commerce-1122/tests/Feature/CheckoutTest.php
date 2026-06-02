<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_guest_checkout_redirects_to_login(): void
    {
        $product = Product::query()->where('slug', 'kindly-mug')->firstOrFail();

        $this->post(route('cart.store'), ['product_id' => $product->id]);

        $this->post(route('checkout.store'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_checkout_and_clear_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('slug', 'kindly-mug')->firstOrFail();
        $initialStock = $product->stock_quantity;

        $this->actingAs($user)
            ->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->post(route('checkout.store'));

        $response->assertRedirect();
        $this->assertStringContainsString('checkout.stripe.test', $response->headers->get('Location'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal_cents' => 2598,
            'discount_cents' => 0,
            'total_cents' => 2598,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price_cents' => 1299,
        ]);

        $product->refresh();
        $this->assertSame($initialStock - 2, $product->stock_quantity);

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertSee('Your cart is empty', false);
    }

    public function test_checkout_rejects_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('slug', 'kindly-notebook')->firstOrFail();
        $product->update(['stock_quantity' => 1]);

        $this->actingAs($user)
            ->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 3]);

        $this->actingAs($user)
            ->post(route('checkout.store'))
            ->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::query()->count());
    }
}
