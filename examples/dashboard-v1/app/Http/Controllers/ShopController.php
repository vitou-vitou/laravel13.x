<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString();

        $categories = Category::query()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('slug')
            ->get();

        $products = Product::query()
            ->with('category')
            ->active()
            ->when(
                $categorySlug !== '',
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($query) => $query->where('slug', $categorySlug),
                ),
            )
            ->orderBy('id')
            ->get();

        return view('shop', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $categorySlug !== '' ? $categorySlug : null,
        ]);
    }

    public function addToCart(Request $request, Product $product, CartService $cartService): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $quantity = max(1, (int) $request->input('quantity', 1));
        $cartService->addItem($request->user(), $product, $quantity);

        return redirect()
            ->route('shop.index', $request->only('category'))
            ->with('cart_added', $product->getTranslation('name', 'en'));
    }
}
