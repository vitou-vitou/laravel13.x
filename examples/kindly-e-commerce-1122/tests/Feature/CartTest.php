<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = Product::query()->where('slug', 'kindly-mug')->firstOrFail();

        $response = $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Kindly Mug', false)
            ->assertSee('$25.98', false);
    }

    public function test_cart_uses_database_price_not_posted_price(): void
    {
        $product = Product::query()->where('slug', 'kindly-mug')->firstOrFail();

        $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'price_cents' => 1,
        ]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('$12.99', false);
    }

    public function test_cart_quantity_can_be_updated_and_removed(): void
    {
        $product = Product::query()->where('slug', 'kindly-tote')->firstOrFail();

        $this->post(route('cart.store'), ['product_id' => $product->id]);

        $this->patch(route('cart.update', $product), ['quantity' => 3])
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))->assertSee('$74.97', false);

        $this->delete(route('cart.destroy', $product))
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))->assertDontSee('Kindly Tote', false);
    }
}
