<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function isWishlisted(User $user, Product $product): bool
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    public function toggle(User $user, Product $product): bool
    {
        $this->assertActiveProduct($product);

        $existing = WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        WishlistItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return true;
    }

    public function remove(User $user, Product $product): void
    {
        WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    /**
     * @return Collection<int, Product>
     */
    public function productsFor(User $user): Collection
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->whereIn('id', function ($query) use ($user) {
                $query->select('product_id')
                    ->from('wishlist_items')
                    ->where('user_id', $user->id);
            })
            ->where('status', ProductStatus::Active)
            ->with(['vendor', 'variants', 'category'])
            ->get();
    }

    public function addFirstVariantToCart(User $user, Product $product, CartService $cart): void
    {
        $this->assertActiveProduct($product);

        $variant = $product->variants()
            ->where('stock_qty', '>', 0)
            ->orderBy('price_cents')
            ->first();

        if ($variant === null) {
            throw ValidationException::withMessages([
                'product' => 'This product is out of stock.',
            ]);
        }

        $cart->add($variant, 1);
    }

    private function assertActiveProduct(Product $product): void
    {
        if ($product->status !== ProductStatus::Active) {
            throw ValidationException::withMessages([
                'product' => 'This product is not available.',
            ]);
        }
    }
}
