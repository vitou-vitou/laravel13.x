<?php

namespace App\Services\Stripe;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

class StripeWebhookHandler
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly RefundService $refunds,
    ) {}

    public function handle(Event $event): void
    {
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            'checkout.session.expired' => $this->handleCheckoutExpired($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'charge.refunded' => $this->handleChargeRefunded($event),
            default => null,
        };
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;
        $order = $this->resolveOrder($session);

        if ($order === null || $order->isPaid() || ! $order->isPending()) {
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

        $this->payments->markPaid(
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
            $order = $order->fresh(['groups.lines.variant']);

            if ($order->isPaid()) {
                return;
            }

            $this->payments->markFailed($order);
        });
    }

    private function handlePaymentFailed(Event $event): void
    {
        $intent = $event->data->object;

        Log::info('Stripe payment_intent.payment_failed', [
            'payment_intent' => $intent->id ?? null,
        ]);
    }

    private function handleChargeRefunded(Event $event): void
    {
        $charge = $event->data->object;
        $intentId = is_string($charge->payment_intent ?? null) ? $charge->payment_intent : null;

        if ($intentId === null) {
            return;
        }

        $payment = Payment::query()
            ->where('stripe_payment_intent_id', $intentId)
            ->first();

        if ($payment === null) {
            return;
        }

        $amountCents = (int) ($charge->amount_refunded ?? 0);
        $refundId = is_string($charge->refunds?->data[0]?->id ?? null)
            ? $charge->refunds->data[0]->id
            : 're_webhook_'.$charge->id;

        $this->refunds->completeFromWebhook(
            $payment,
            $amountCents,
            $refundId,
            'Stripe charge.refunded webhook',
        );
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
