<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SendsStripeWebhooks;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;
    use SendsStripeWebhooks;

    public function test_webhook_marks_order_paid_on_checkout_session_completed(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $order = $this->pendingOrder($user, 'cs_test_123', 1000);

        $this->postStripeWebhook($this->completedEvent($order, 'cs_test_123', 1000))
            ->assertOk();

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->paid_at);
        $this->assertSame(PaymentStatus::Completed, $order->payment->status);
        $this->assertSame('pi_test_abc', $order->payment->stripe_payment_intent_id);
    }

    public function test_duplicate_completed_webhooks_are_idempotent(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Paid,
            'total_cents' => 1000,
            'stripe_checkout_session_id' => 'cs_test_123',
            'paid_at' => now(),
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::Completed,
            'amount_cents' => 1000,
            'stripe_payment_intent_id' => 'pi_existing',
        ]);

        $this->postStripeWebhook($this->completedEvent($order, 'cs_test_123', 1000))
            ->assertOk();

        $this->assertSame('pi_existing', $order->fresh()->payment->stripe_payment_intent_id);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $order = $this->pendingOrder($user, 'cs_test_123', 1000);

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
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $order = $this->pendingOrder($user, 'cs_test_123', 1000);

        $this->postStripeWebhook($this->completedEvent($order, 'cs_test_123', 999))
            ->assertOk();

        $this->assertTrue($order->fresh()->isPending());
    }

    private function pendingOrder(User $user, string $sessionId, int $totalCents): Order
    {
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PendingPayment,
            'total_cents' => $totalCents,
            'stripe_checkout_session_id' => $sessionId,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::Pending,
            'amount_cents' => $totalCents,
        ]);

        return $order;
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
