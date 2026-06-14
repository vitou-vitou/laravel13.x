<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class RecentlyViewedService
{
    private const SESSION_KEY = 'recently_viewed_product_ids';

    private const MAX_ITEMS = 8;

    public function record(int $productId): void
    {
        $ids = collect(session(self::SESSION_KEY, []))
            ->prepend($productId)
            ->unique()
            ->take(self::MAX_ITEMS)
            ->values()
            ->all();

        session([self::SESSION_KEY => $ids]);
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(): Collection
    {
        $ids = session(self::SESSION_KEY, []);

        if ($ids === []) {
            return collect();
        }

        $products = Product::query()
            ->withoutGlobalScopes()
            ->where('status', ProductStatus::Active)
            ->whereIn('id', $ids)
            ->with(['vendor', 'variants', 'category'])
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id) => $products->get($id))
            ->filter();
    }

    /**
     * @return Collection<int, Category>
     */
    public function featuredCategories(int $limit = 6): Collection
    {
        return Category::query()
            ->whereHas('products', fn ($query) => $query
                ->withoutGlobalScopes()
                ->where('status', ProductStatus::Active))
            ->withCount(['products' => fn ($query) => $query
                ->withoutGlobalScopes()
                ->where('status', ProductStatus::Active)])
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get();
    }
}
