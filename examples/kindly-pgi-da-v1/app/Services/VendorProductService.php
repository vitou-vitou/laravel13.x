<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorProductService
{
    public const LOW_STOCK_THRESHOLD = 5;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Vendor $vendor, array $data): Product
    {
        return DB::transaction(function () use ($vendor, $data) {
            $product = Product::query()->create([
                'vendor_id' => $vendor->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'],
                'status' => ProductStatus::from($data['status']),
            ]);

            $this->syncVariants($product, $data['variants']);

            return $product->fresh(['variants', 'category']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => ProductStatus::from($data['status']),
            ]);

            $this->syncVariants($product, $data['variants']);

            return $product->fresh(['variants', 'category']);
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, ProductVariant>
     */
    public function lowStockVariantsFor(Vendor $vendor)
    {
        return ProductVariant::query()
            ->whereHas('product', fn ($query) => $query
                ->withoutGlobalScopes()
                ->where('vendor_id', $vendor->id)
                ->where('status', ProductStatus::Active))
            ->where('stock_qty', '<=', self::LOW_STOCK_THRESHOLD)
            ->with(['product'])
            ->orderBy('stock_qty')
            ->limit(10)
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $keptIds = [];

        foreach ($variants as $index => $row) {
            $sku = $row['sku'] ?? $this->generateSku($product, $index);

            if (! empty($row['id'])) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->whereKey($row['id'])
                    ->firstOrFail();

                $variant->update([
                    'sku' => $sku,
                    'name' => $row['name'],
                    'price_cents' => (int) $row['price_cents'],
                    'stock_qty' => (int) $row['stock_qty'],
                ]);

                $keptIds[] = $variant->id;

                continue;
            }

            $variant = ProductVariant::query()->create([
                'product_id' => $product->id,
                'sku' => $this->ensureUniqueSku($product, $sku),
                'name' => $row['name'],
                'price_cents' => (int) $row['price_cents'],
                'stock_qty' => (int) $row['stock_qty'],
            ]);

            $keptIds[] = $variant->id;
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Product::query()->withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function generateSku(Product $product, int $index): string
    {
        return strtoupper(Str::slug($product->slug, '-')).'-'.($index + 1);
    }

    private function ensureUniqueSku(Product $product, string $sku): string
    {
        $candidate = $sku;
        $i = 1;

        while (ProductVariant::query()->where('product_id', $product->id)->where('sku', $candidate)->exists()) {
            $candidate = $sku.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
