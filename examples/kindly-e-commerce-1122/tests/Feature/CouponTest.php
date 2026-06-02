<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\CouponSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
        $this->seed(CouponSeeder::class);
    }

    public function test_valid_percent_coupon_reduces_cart_total(): void
    {
        $product = Product::query()->where('slug', 'kindly-mug')->firstOrFail();
        $this->post(route('cart.store'), ['product_id' => $product->id]);

        $this->post(route('cart.coupon.store'), ['code' => 'kindly10'])
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('KINDLY10', false)
            ->assertSee('$11.69', false);
    }

    public function test_invalid_coupon_is_rejected(): void
    {
        $this->post(route('cart.coupon.store'), ['code' => 'NOTREAL'])
            ->assertSessionHasErrors('code');
    }

    public function test_checkout_persists_coupon_discount_on_order(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('slug', 'kindly-mug')->firstOrFail();

        $this->actingAs($user)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($user)->post(route('cart.coupon.store'), ['code' => 'SAVE500']);

        $this->actingAs($user)->post(route('checkout.store'))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'subtotal_cents' => 2598,
            'discount_cents' => 500,
            'coupon_code' => 'SAVE500',
            'total_cents' => 2098,
        ]);
    }
}
