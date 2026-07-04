<?php

namespace App\Http\Controllers;

use App\Contracts\CreatesStripeCheckoutSession;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CheckoutController extends Controller implements HasMiddleware
{
    public function __construct(
        private CheckoutService $checkout,
        private CreatesStripeCheckoutSession $stripeCheckout,
    ) {}

    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_address_id' => ['nullable', 'integer', 'exists:shipping_addresses,id'],
        ]);

        $order = $this->checkout->placeFromCart($validated['shipping_address_id'] ?? null);
        $checkoutUrl = $this->stripeCheckout->createForOrder($order);

        if (str_starts_with($checkoutUrl, 'http')) {
            return redirect()->away($checkoutUrl);
        }

        return redirect($checkoutUrl);
    }

    public function success(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return view('checkout.success', compact('order'));
    }

    public function cancel(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return view('checkout.cancel', compact('order'));
    }
}
