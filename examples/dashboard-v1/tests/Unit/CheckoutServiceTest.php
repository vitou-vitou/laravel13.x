<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_throws_when_cart_is_empty(): void
    {
        $user = User::factory()->create();
        $service = new CheckoutService(new CartService);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cart is empty.');

        $service->checkout($user);
    }

    public function test_checkout_creates_order_payment_and_clears_cart(): void
    {
        $user = User::factory()->create([
            'name' => 'Checkout User',
            'email' => 'checkout@example.com',
        ]);

        $product = Product::factory()->create(['price_cents' => 3_000]);
        $cartService = new CartService;
        $cartService->addItem($user, $product, 2);

        $order = (new CheckoutService($cartService))->checkout($user);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame(6_000, $order->amount_cents);
        $this->assertSame('paid', $order->status);
        $this->assertSame('Checkout User', $order->customer->name);
        $this->assertSame('checkout@example.com', $order->customer->email);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount_cents' => 6_000,
            'status' => 'completed',
            'method' => 'card',
        ]);

        $payment = Payment::query()->where('order_id', $order->id)->first();
        $this->assertNotNull($payment->reference);

        $this->assertSame(0, $cartService->totalCents($user));

        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->getTranslation('name', 'en'),
            'quantity' => 2,
            'unit_price_cents' => 3_000,
            'line_total_cents' => 6_000,
        ]);

        $this->assertCount(1, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }
}
