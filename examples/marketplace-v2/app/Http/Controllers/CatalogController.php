<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogQueryService;
use App\Services\RecentlyViewedService;
use App\Services\WishlistService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request, CatalogQueryService $catalog, RecentlyViewedService $recentlyViewed): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $products = $catalog->paginate(12);
        $isHome = $request->routeIs('home');

        return view('catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'isHome' => $isHome,
            'featuredCategories' => $isHome ? $recentlyViewed->featuredCategories() : collect(),
            'recentlyViewed' => $isHome ? $recentlyViewed->products() : collect(),
            'sort' => $catalog->sort(),
            'minPrice' => $request->input('min_price'),
            'maxPrice' => $request->input('max_price'),
        ]);
    }

    public function show(int $product, WishlistService $wishlist, RecentlyViewedService $recentlyViewed): View
    {
        $product = Product::query()
            ->withoutGlobalScopes()
            ->where('status', ProductStatus::Active)
            ->whereKey($product)
            ->with(['vendor', 'variants', 'category', 'reviews.user'])
            ->firstOrFail();

        $recentlyViewed->record($product->id);

        $isWishlisted = auth()->check() && $wishlist->isWishlisted(auth()->user(), $product);

        return view('catalog.show', compact('product', 'isWishlisted'));
    }
}
