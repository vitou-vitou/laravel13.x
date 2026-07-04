<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function create(User $user, Product $product, Order $order, int $rating, string $body): Review
    {
        if ($order->user_id !== $user->id || ! $order->isPaid()) {
            throw ValidationException::withMessages([
                'order' => 'You can only review products from your paid orders.',
            ]);
        }

        $ownsProduct = $order->groups()
            ->whereHas('lines.variant.product', fn ($q) => $q->whereKey($product->id))
            ->exists();

        if (! $ownsProduct) {
            throw ValidationException::withMessages([
                'product' => 'This product was not part of the order.',
            ]);
        }

        if (Review::query()->where('user_id', $user->id)->where('product_id', $product->id)->where('order_id', $order->id)->exists()) {
            throw ValidationException::withMessages([
                'product' => 'You already reviewed this product for this order.',
            ]);
        }

        return DB::transaction(function () use ($user, $product, $order, $rating, $body) {
            $review = Review::query()->create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'rating' => $rating,
                'body' => $body,
            ]);

            $this->refreshVendorRating($product->vendor_id);

            return $review;
        });
    }

    private function refreshVendorRating(int $vendorId): void
    {
        $stats = Review::query()
            ->whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        Vendor::query()->whereKey($vendorId)->update([
            'rating_avg' => round((float) ($stats->avg_rating ?? 0), 2),
            'rating_count' => (int) ($stats->total ?? 0),
        ]);
    }
}
