<?php

namespace App\Services\Stripe;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Models\Order;

class FakeStripeCheckoutService implements CreatesStripeCheckoutSession
{
    public function createForOrder(Order $order): string
    {
        $sessionId = 'cs_test_fake_'.$order->id;

        $order->update([
            'stripe_checkout_session_id' => $sessionId,
        ]);

        return 'https://checkout.stripe.test/pay/'.$sessionId;
    }
}
