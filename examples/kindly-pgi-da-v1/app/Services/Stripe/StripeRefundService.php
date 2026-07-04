<?php

namespace App\Services\Stripe;

use App\Contracts\CreatesStripeRefund;
use App\Models\Payment;
use App\Models\Refund;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;

class StripeRefundService implements CreatesStripeRefund
{
    public function create(Payment $payment, Refund $refund): string
    {
        Stripe::setApiKey(config('stripe.secret'));

        $intentId = $payment->stripe_payment_intent_id;

        if ($intentId === null || $intentId === '') {
            throw new \RuntimeException('Payment has no Stripe payment intent id.');
        }

        $stripeRefund = StripeRefund::create([
            'payment_intent' => $intentId,
            'amount' => $refund->amount_cents,
            'metadata' => [
                'order_id' => (string) $refund->order_id,
                'refund_id' => (string) $refund->id,
            ],
        ]);

        return $stripeRefund->id;
    }
}
