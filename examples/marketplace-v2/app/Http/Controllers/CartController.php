<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'totalCents' => $this->cart->totalCents(),
            'vendorSubtotals' => $this->cart->vendorSubtotals(),
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
}
