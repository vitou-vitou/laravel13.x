<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\PromoCodeService;
use App\Services\ShippingAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private PromoCodeService $promoCodes,
        private ShippingAddressService $shippingAddresses,
    ) {}

    public function index(): View
    {
        $subtotalCents = $this->cart->totalCents();
        $lines = $this->cart->lines();
        $promo = $this->promoCodes->appliedPromo();
        $discountCents = $promo ? $this->promoCodes->discountCents($promo, $subtotalCents, $lines) : 0;

        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'subtotalCents' => $subtotalCents,
            'discountCents' => $discountCents,
            'totalCents' => max(0, $subtotalCents - $discountCents),
            'appliedPromo' => $promo,
            'vendorSubtotals' => $this->cart->vendorSubtotals(),
            'shippingAddresses' => auth()->check()
                ? $this->shippingAddresses->forUser(auth()->user())
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $variant = ProductVariant::query()
            ->with('product')
            ->findOrFail($validated['product_variant_id']);

        $this->cart->add($variant, $validated['quantity'] ?? 1);

        return redirect()->route('cart.index')->with('status', 'Added to cart.');
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->update($variant, $validated['quantity']);

        return redirect()->route('cart.index')->with('status', 'Cart updated.');
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $this->cart->remove($variant);

        return redirect()->route('cart.index')->with('status', 'Item removed.');
    }

    public function applyPromo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40'],
        ]);

        $this->promoCodes->applyToSession($validated['code'], $this->cart->totalCents(), $this->cart->lines());

        return redirect()->route('cart.index')->with('status', 'Promo code applied.');
    }

    public function removePromo(): RedirectResponse
    {
        $this->promoCodes->clearSession();

        return redirect()->route('cart.index')->with('status', 'Promo code removed.');
    }
}
