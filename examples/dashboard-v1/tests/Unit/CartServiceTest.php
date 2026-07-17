<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_gets_a_persisted_cart(): void
    {
        $user = User::factory()->create();
        $service = new CartService;

        $cart = $service->forUser($user);

        $this->assertSame($user->id, $cart->user_id);
        $this->assertSame($cart->id, $service->forUser($user)->id);
    }

    public function test_add_item_increments_quantity_and_total(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price_cents' => 2_000]);
        $service = new CartService;

        $service->addItem($user, $product, 2);
        $service->addItem($user, $product, 1);

        $this->assertSame(6_000, $service->totalCents($user));
    }

    public function test_clear_removes_all_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $service = new CartService;

        $service->addItem($user, $product);
        $service->clear($user);

        $this->assertSame(0, $service->totalCents($user));
    }
}
