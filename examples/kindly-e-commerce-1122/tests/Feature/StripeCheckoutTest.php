<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SendsStripeWebhooks;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;
    use SendsStripeWebhooks;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_checkout_creates_pending_order_and_redirects_to_stripe(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->where('slug', 'kindly-mug')->firstOrFail();
        $initialStock = $product->stock_quantity;

        $this->actingAs($user)
            ->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->post(route('checkout.store'));

        $response->assertRedirect();
        $this->assertStringContainsString('checkout.stripe.test', $response->headers->get('Location'));

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertTrue($order->isPending());
        $this->assertSame('cs_test_fake_'.$order->id, $order->stripe_checkout_session_id);
        $this->assertSame(2598, $order->total_cents);

        $product->refresh();
        $this->assertSame($initialStock - 2, $product->stock_quantity);
    }

    public function test_success_page_does_not_mark_order_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_fake_1',
        ]);

        $this->actingAs($user)
            ->get(route('checkout.success', $order))
            ->assertOk()
            ->assertSee('does not mark your order as paid', false);

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_stub_pay_route_is_removed(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
        ]);

        $this->actingAs($user)
            ->post('/orders/'.$order->id.'/pay')
            ->assertNotFound();
    }
}
