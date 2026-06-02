<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Stripe\LocalDevStripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class DevStripeSimulateController extends Controller
{
    public function store(Order $order): RedirectResponse|Response
    {
        if (! app()->environment('local')) {
            abort(404);
        }

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! LocalDevStripeCheckoutService::isLocalDevSession($order->stripe_checkout_session_id)) {
            abort(404);
        }

        if ($order->isPaid()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('status', 'Order is already paid.');
        }

        $order->update([
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_local_dev_simulated',
        ]);

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Payment simulated (local dev only).');
    }
}
