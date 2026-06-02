<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * @return Collection<int, array{product: Product, quantity: int, line_total_cents: int}>
     */
    public function lines(): Collection
    {
        $quantities = $this->quantities();

        if ($quantities === []) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', array_keys($quantities))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        return collect($quantities)
            ->map(function (int $quantity, int $productId) use ($products) {
                $product = $products->get($productId);

                if ($product === null) {
                    return null;
                }

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total_cents' => $product->price_cents * $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function totalCents(): int
    {
        return $this->lines()->sum('line_total_cents');
    }

    public function isEmpty(): bool
    {
        return $this->quantities() === [];
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $quantities = $this->quantities();
        $current = $quantities[$product->id] ?? 0;
        $quantities[$product->id] = $current + max(1, $quantity);
        $this->persist($quantities);
    }

    public function update(Product $product, int $quantity): void
    {
        $quantities = $this->quantities();

        if ($quantity <= 0) {
            unset($quantities[$product->id]);
        } else {
            $quantities[$product->id] = $quantity;
        }

        $this->persist($quantities);
    }

    public function remove(Product $product): void
    {
        $quantities = $this->quantities();
        unset($quantities[$product->id]);
        $this->persist($quantities);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return array<int, int>
     */
    public function quantities(): array
    {
        /** @var array<int, int>|null $cart */
        $cart = Session::get(self::SESSION_KEY);

        if (! is_array($cart)) {
            return [];
        }

        $normalized = [];

        foreach ($cart as $productId => $quantity) {
            $id = (int) $productId;
            $qty = (int) $quantity;

            if ($id > 0 && $qty > 0) {
                $normalized[$id] = $qty;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<int, int>  $quantities
     */
    private function persist(array $quantities): void
    {
        Session::put(self::SESSION_KEY, $quantities);
    }
}
