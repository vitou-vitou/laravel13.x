<?php

namespace Tests\Unit;

use App\Mail\NewOrderMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderMailNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderMailNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_queues_new_order_mail_to_admins(): void
    {
        Mail::fake();

        $admin = $this->adminUser(['email' => 'admin@example.com']);
        $this->adminUser(['email' => 'ops@example.com']);

        $customer = User::factory()->create();
        $this->seedRoles();
        $customer->assignRole('customer');

        $product = Product::factory()->create(['price_cents' => 1_500]);
        $cartService = new CartService;
        $cartService->addItem($customer, $product, 1);

        $checkout = new CheckoutService($cartService, new OrderMailNotifier);
        $order = $checkout->checkout($customer);

        Mail::assertQueued(NewOrderMail::class, 2);
        Mail::assertQueued(NewOrderMail::class, fn (NewOrderMail $mail) => $mail->hasTo('admin@example.com') && $mail->order->is($order));
        Mail::assertQueued(NewOrderMail::class, fn (NewOrderMail $mail) => $mail->hasTo('ops@example.com'));
    }

    public function test_checkout_does_not_queue_mail_when_no_admins(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['price_cents' => 500]);
        $cartService = new CartService;
        $cartService->addItem($user, $product, 1);

        (new CheckoutService($cartService, new OrderMailNotifier))->checkout($user);

        Mail::assertNothingQueued();
    }

    public function test_new_order_mail_subject_includes_order_id(): void
    {
        $order = Order::factory()->paid()->create(['amount_cents' => 2_000]);

        $mail = new NewOrderMail($order->load(['customer', 'items']));

        $this->assertSame('New order #'.$order->id, $mail->envelope()->subject);
    }
}
