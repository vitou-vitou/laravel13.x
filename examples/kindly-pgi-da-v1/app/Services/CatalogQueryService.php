<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogQueryService
{
    public const SORT_NEWEST = 'newest';

    public const SORT_PRICE_ASC = 'price_asc';

    public const SORT_PRICE_DESC = 'price_desc';

    public function __construct(private Request $request) {}

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        $search = $this->request->string('q');

        if ($search->isNotEmpty()) {
            $ids = Product::search($search->toString())->get()->pluck('id');
            $builder = $this->baseQuery()->whereIn('id', $ids);
        } else {
            $builder = $this->baseQuery();
        }

        $this->applyCategoryFilter($builder);
        $this->applyPriceFilters($builder);
        $this->applySort($builder);

        return $builder->paginate($perPage)->withQueryString();
    }

    public function sort(): string
    {
        $sort = $this->request->string('sort')->toString();

        return in_array($sort, [self::SORT_NEWEST, self::SORT_PRICE_ASC, self::SORT_PRICE_DESC], true)
            ? $sort
            : self::SORT_NEWEST;
    }

    public function minPriceCents(): ?int
    {
        return $this->request->filled('min_price')
            ? max(0, (int) $this->request->input('min_price')) * 100
            : null;
    }

    public function maxPriceCents(): ?int
    {
        return $this->request->filled('max_price')
            ? max(0, (int) $this->request->input('max_price')) * 100
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function queryParamsExcept(array $except = []): array
    {
        return collect($this->request->query())
            ->except($except)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }

    private function baseQuery(): Builder
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->where('status', ProductStatus::Active)
            ->with(['vendor', 'variants', 'category']);
    }

    private function applyCategoryFilter(Builder $builder): void
    {
        if ($this->request->filled('category')) {
            $slug = $this->request->string('category');
            $builder->whereHas('category', fn ($query) => $query->where('slug', $slug));
        }
    }

    private function applyPriceFilters(Builder $builder): void
    {
        $min = $this->minPriceCents();
        $max = $this->maxPriceCents();

        if ($min === null && $max === null) {
            return;
        }

        $builder->whereHas('variants', function (Builder $query) use ($min, $max): void {
            if ($min !== null) {
                $query->where('price_cents', '>=', $min);
            }
            if ($max !== null) {
                $query->where('price_cents', '<=', $max);
            }
        });
    }

    private function applySort(Builder $builder): void
    {
        if (in_array($this->sort(), [self::SORT_PRICE_ASC, self::SORT_PRICE_DESC], true)) {
            $direction = $this->sort() === self::SORT_PRICE_DESC ? 'desc' : 'asc';

            $builder
                ->joinSub(
                    ProductVariant::query()
                        ->selectRaw('product_id, MIN(price_cents) as catalog_min_price')
                        ->groupBy('product_id'),
                    'catalog_price_sort',
                    'catalog_price_sort.product_id',
                    '=',
                    'products.id'
                )
                ->orderBy('catalog_price_sort.catalog_min_price', $direction)
                ->select('products.*');

            return;
        }

        $builder->latest();
    }
}
