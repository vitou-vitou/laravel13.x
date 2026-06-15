<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class WishlistController extends Controller implements HasMiddleware
{
    public function __construct(
        private WishlistService $wishlist,
        private CartService $cart,
    ) {}

    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(): View
    {
        return view('wishlist.index', [
            'products' => $this->wishlist->productsFor(auth()->user()),
        ]);
    }

    public function store(Product $product): RedirectResponse
    {
        $product = Product::query()
            ->withoutGlobalScopes()
            ->where('status', ProductStatus::Active)
            ->whereKey($product->id)
            ->firstOrFail();

        $added = $this->wishlist->toggle(auth()->user(), $product);

        return back()->with('status', $added ? 'Saved to wishlist.' : 'Removed from wishlist.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->wishlist->remove(auth()->user(), $product);

        return back()->with('status', 'Removed from wishlist.');
    }

    public function addToCart(Product $product): RedirectResponse
    {
        $product = Product::query()
            ->withoutGlobalScopes()
            ->where('status', ProductStatus::Active)
            ->whereKey($product->id)
            ->with('variants')
            ->firstOrFail();

        $this->wishlist->addFirstVariantToCart(auth()->user(), $product, $this->cart);

        return redirect()->route('cart.index')->with('status', 'Added to cart.');
    }
}
