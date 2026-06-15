<?php

namespace App\Services\Stripe;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutService implements CreatesStripeCheckoutSession
{
    public function createForOrder(Order $order): string
    {
        $secret = config('stripe.secret');

        if (! is_string($secret) || $secret === '') {
            throw new \RuntimeException('STRIPE_SECRET is not configured.');
        }

        Stripe::setApiKey($secret);

        $session = Session::create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => config('stripe.currency', 'usd'),
                    'unit_amount' => $order->total_cents,
                    'product_data' => [
                        'name' => 'Marketplace order #'.$order->id,
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'order_id' => (string) $order->id,
            ],
            'client_reference_id' => (string) $order->id,
            'success_url' => route('checkout.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', $order),
        ]);

        $order->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return $session->url;
    }
}
