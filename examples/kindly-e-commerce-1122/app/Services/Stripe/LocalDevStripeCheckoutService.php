<?php

namespace App\Services\Stripe;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Models\Order;

/**
 * Used when STRIPE_SECRET is missing in local — avoids 500s during manual QA.
 * Does not call Stripe; order stays pending until webhook or dev simulate route.
 */
class LocalDevStripeCheckoutService implements CreatesStripeCheckoutSession
{
    public const SESSION_PREFIX = 'cs_local_dev_';

    public function createForOrder(Order $order): string
    {
        $order->update([
            'stripe_checkout_session_id' => self::SESSION_PREFIX.$order->id,
        ]);

        return route('checkout.success', $order);
    }

    public static function isLocalDevSession(?string $sessionId): bool
    {
        return is_string($sessionId) && str_starts_with($sessionId, self::SESSION_PREFIX);
    }
}
