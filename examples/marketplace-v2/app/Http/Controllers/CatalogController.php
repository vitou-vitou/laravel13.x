<?php

namespace App\Http\Controllers;

use App\Enums\OrderGroupStatus;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $query = $request->string('q');

        if ($query->isNotEmpty()) {
            $ids = Product::search($query->toString())
                ->get()
                ->pluck('id');

            $products = Product::query()
                ->withoutGlobalScopes()
                ->whereIn('id', $ids)
                ->where('status', ProductStatus::Active)
                ->with(['vendor', 'variants', 'category'])
                ->when($request->filled('category'), function ($builder) use ($request) {
                    $builder->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
                })
                ->paginate(12);
        } else {
            $products = Product::query()
                ->withoutGlobalScopes()
                ->where('status', ProductStatus::Active)
                ->with(['vendor', 'variants', 'category'])
                ->when($request->filled('category'), function ($builder) use ($request) {
                    $builder->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
                })
                ->latest()
                ->paginate(12);
        }

        return view('catalog.index', compact('products', 'categories'));
    }

    public function show(int $product): View
    {
        $product = Product::query()
            ->withoutGlobalScopes()
            ->where('status', ProductStatus::Active)
            ->whereKey($product)
            ->with(['vendor', 'variants', 'category', 'reviews.user'])
            ->firstOrFail();

        return view('catalog.show', compact('product'));
    }
}
