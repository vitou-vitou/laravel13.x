<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class CartController extends Controller
{
    public function show(CartService $cartService): View
    {
        $cart = $cartService->forUser(auth()->user());
        $cart->load(['items.product.category']);

        return view('cart', [
            'cart' => $cart,
            'totalCents' => $cartService->totalCents(auth()->user()),
        ]);
    }

    public function checkout(CheckoutService $checkoutService): RedirectResponse
    {
        try {
            $order = $checkoutService->checkout(auth()->user());
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('cart')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('dashboard')
            ->with('checkout_order_id', $order->id)
            ->with('checkout_total', $order->formattedAmount())
            ->with('checkout_customer', $order->customer->name);
    }
}
