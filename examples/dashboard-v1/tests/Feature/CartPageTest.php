<?php

namespace Tests\Feature;

use App\Mail\NewOrderMail;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_cart(): void
    {
        $this->get(route('cart'))->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_cart_items(): void
    {
        $user = $this->adminUser();
        $product = Product::factory()->create([
            'name' => ['en' => 'Demo Product', 'es' => 'Producto demo'],
        ]);

        (new CartService)->addItem($user, $product, 2);

        $this->actingAs($user)
            ->get(route('cart'))
            ->assertOk()
            ->assertSee('Demo Product')
            ->assertSee('Qty: 2')
            ->assertSee('Place order');
    }

    public function test_customer_can_checkout_cart(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();
        $user->assignRole('customer');

        $product = Product::factory()->create([
            'price_cents' => 2_500,
            'name' => ['en' => 'Widget', 'es' => 'Widget'],
        ]);

        (new CartService)->addItem($user, $product, 1);

        $this->actingAs($user)
            ->post(route('cart.checkout'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('checkout_order_id')
            ->assertSessionHas('checkout_customer', $user->name)
            ->assertSessionHas('checkout_total');

        $this->assertDatabaseHas('orders', ['amount_cents' => 2_500, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', ['amount_cents' => 2_500, 'status' => 'completed']);
        $this->assertSame(0, (new CartService)->totalCents($user));
    }

    public function test_checkout_queues_admin_notification_email(): void
    {
        Mail::fake();

        $this->seedRoles();
        $this->adminUser(['email' => 'store-admin@example.com']);

        $user = User::factory()->create();
        $user->assignRole('customer');

        $product = Product::factory()->create(['price_cents' => 900]);
        (new CartService)->addItem($user, $product, 1);

        $this->actingAs($user)->post(route('cart.checkout'));

        Mail::assertQueued(NewOrderMail::class, fn (NewOrderMail $mail) => $mail->hasTo('store-admin@example.com'));
    }

    public function test_checkout_empty_cart_redirects_with_error(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('cart.checkout'))
            ->assertRedirect(route('cart'))
            ->assertSessionHas('error', 'Cart is empty.');
    }
}
