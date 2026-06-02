<?php

namespace App\Services;

use App\Mail\OrderPaidMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderLifecycleService
{
    public function markPaid(Order $order, ?string $paymentIntentId = null): void
    {
        if (! $order->isPending()) {
            return;
        }

        $order->update([
            'status' => 'paid',
            'stripe_payment_intent_id' => $paymentIntentId ?? $order->stripe_payment_intent_id,
            'paid_at' => now(),
        ]);

        $order->loadMissing('user');
        Mail::to($order->user)->queue(new OrderPaidMail($order));
    }

    public function markShipped(Order $order): bool
    {
        if (! $order->isPaid()) {
            return false;
        }

        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        $order->loadMissing('user');
        Mail::to($order->user)->queue(new OrderShippedMail($order));

        return true;
    }
}
