<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
    ) {}

    public function index(): View
    {
        $subtotalCents = $this->cart->totalCents();
        $discountCents = $this->coupons->discountCents($subtotalCents);

        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'subtotalCents' => $subtotalCents,
            'discountCents' => $discountCents,
            'totalCents' => max(0, $subtotalCents - $discountCents),
            'appliedCoupon' => $this->coupons->appliedCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::query()
            ->where('is_active', true)
            ->findOrFail($validated['product_id']);

        $this->cart->add($product, $validated['quantity'] ?? 1);

        return redirect()->route('cart.index')->with('status', 'Added to cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->update($product, $validated['quantity']);

        return redirect()->route('cart.index')->with('status', 'Cart updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()->route('cart.index')->with('status', 'Item removed.');
    }
}
