<?php

namespace App\Services\Stripe;

use App\Models\Order;
use App\Services\OrderLifecycleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

class StripeWebhookHandler
{
    public function __construct(private readonly OrderLifecycleService $lifecycle)
    {
    }

    public function handle(Event $event): void
    {
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            'checkout.session.expired' => $this->handleCheckoutExpired($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            default => null,
        };
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;
        $order = $this->resolveOrder($session);

        if ($order === null) {
            return;
        }

        if ($order->isPaid()) {
            return;
        }

        if (! $order->isPending()) {
            return;
        }

        if ($order->stripe_checkout_session_id !== null
            && $order->stripe_checkout_session_id !== $session->id) {
            Log::warning('Stripe session id mismatch for order', [
                'order_id' => $order->id,
                'expected' => $order->stripe_checkout_session_id,
                'received' => $session->id,
            ]);

            return;
        }

        $amountTotal = (int) ($session->amount_total ?? 0);

        if ($amountTotal !== $order->total_cents) {
            Log::warning('Stripe amount mismatch for order', [
                'order_id' => $order->id,
                'expected' => $order->total_cents,
                'received' => $amountTotal,
            ]);

            return;
        }

        $order->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        $this->lifecycle->markPaid(
            $order,
            is_string($session->payment_intent ?? null) ? $session->payment_intent : null,
        );
    }

    private function handleCheckoutExpired(Event $event): void
    {
        $session = $event->data->object;
        $order = $this->resolveOrder($session);

        if ($order === null || $order->isPaid()) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order = $order->fresh(['items.product']);

            if ($order->isPaid()) {
                return;
            }

            $order->restoreStock();
        });
    }

    private function handlePaymentFailed(Event $event): void
    {
        $intent = $event->data->object;

        Log::info('Stripe payment_intent.payment_failed', [
            'payment_intent' => $intent->id ?? null,
        ]);
    }

    private function resolveOrder(object $stripeObject): ?Order
    {
        $orderId = $stripeObject->metadata->order_id
            ?? $stripeObject->client_reference_id
            ?? null;

        if ($orderId === null || $orderId === '') {
            return null;
        }

        return Order::query()->find((int) $orderId);
    }
}
