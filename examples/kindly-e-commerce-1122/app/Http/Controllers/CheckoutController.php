<?php

namespace App\Http\Controllers;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Models\Order;
use App\Services\OrderPlacementService;
use App\Services\Stripe\LocalDevStripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private OrderPlacementService $placement,
        private CreatesStripeCheckoutSession $stripeCheckout,
    ) {}

    public function store(): RedirectResponse
    {
        $order = $this->placement->placeFromCart();
        $checkoutUrl = $this->stripeCheckout->createForOrder($order);
        $order->refresh();

        if (LocalDevStripeCheckoutService::isLocalDevSession($order->stripe_checkout_session_id)) {
            return redirect()
                ->route('checkout.success', $order)
                ->with('status', 'Stripe is not configured — using local dev checkout (order stays pending until you simulate payment below).');
        }

        return redirect()->away($checkoutUrl);
    }

    public function success(Order $order): View|Response
    {
        $this->authorizeOrder($order);

        return view('checkout.success', compact('order'));
    }

    public function cancel(Order $order): View|Response
    {
        $this->authorizeOrder($order);

        return view('checkout.cancel', compact('order'));
    }

    private function authorizeOrder(Order $order): void
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
