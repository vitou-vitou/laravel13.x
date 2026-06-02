<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Mail\OrderPaidMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\SendsStripeWebhooks;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;
    use SendsStripeWebhooks;

    public function test_webhook_marks_order_paid_on_checkout_session_completed(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_cents' => 1000,
        ]);

        $this->postStripeWebhook($this->completedEvent($order, 'cs_test_123', 1000))
            ->assertOk();

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->paid_at);
        $this->assertSame('pi_test_abc', $order->stripe_payment_intent_id);
        Mail::assertQueued(OrderPaidMail::class, 1);
    }

    public function test_duplicate_completed_webhooks_are_idempotent(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_123',
            'stripe_payment_intent_id' => 'pi_existing',
        ]);

        $this->postStripeWebhook($this->completedEvent($order, 'cs_test_123', 1000))
            ->assertOk();

        $this->assertSame('pi_existing', $order->fresh()->stripe_payment_intent_id);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        $payload = json_encode($this->completedEvent($order, 'cs_test_123', 1000));

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => 'invalid',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertStatus(400);

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_webhook_rejects_amount_mismatch(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        $this->postStripeWebhook($this->completedEvent($order, 'cs_test_123', 999))
            ->assertOk();

        $this->assertTrue($order->fresh()->isPending());
    }

    /**
     * @return array<string, mixed>
     */
    private function completedEvent(Order $order, string $sessionId, int $amountTotal): array
    {
        return [
            'id' => 'evt_test_'.uniqid(),
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'object' => 'checkout.session',
                    'amount_total' => $amountTotal,
                    'payment_intent' => 'pi_test_abc',
                    'metadata' => [
                        'order_id' => (string) $order->id,
                    ],
                    'client_reference_id' => (string) $order->id,
                ],
            ],
        ];
    }
}
