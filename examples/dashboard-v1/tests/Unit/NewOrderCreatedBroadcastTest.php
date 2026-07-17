<?php

namespace Tests\Unit;

use App\Events\NewOrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderMailNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NewOrderCreatedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_dispatches_new_order_created_event(): void
    {
        Event::fake([NewOrderCreated::class]);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price_cents' => 1_200]);
        $cartService = new CartService;
        $cartService->addItem($user, $product, 1);

        $order = (new CheckoutService($cartService, new OrderMailNotifier))->checkout($user);

        Event::assertDispatched(NewOrderCreated::class, fn (NewOrderCreated $event) => $event->order->is($order));
    }

    public function test_new_order_created_broadcast_payload(): void
    {
        $order = Order::factory()->paid()->create(['amount_cents' => 4_500]);

        $event = new NewOrderCreated($order->load('customer'));

        $this->assertSame('NewOrderCreated', $event->broadcastAs());
        $this->assertSame([
            'orderId' => $order->id,
            'customer' => $order->customer->name,
            'amount' => $order->formattedAmount(),
        ], $event->broadcastWith());
    }
}
