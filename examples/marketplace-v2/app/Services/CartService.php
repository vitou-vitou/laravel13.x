<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartLine;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function cart(): Cart
    {
        if (auth()->check()) {
            return Cart::query()->firstOrCreate(
                ['user_id' => auth()->id(), 'status' => 'active'],
                ['session_id' => null],
            );
        }

        $sessionId = session()->getId();

        return Cart::query()->firstOrCreate(
            ['session_id' => $sessionId, 'status' => 'active', 'user_id' => null],
            [],
        );
    }

    /** @return Collection<int, CartLine> */
    public function lines(): Collection
    {
        return $this->cart()
            ->lines()
            ->with(['variant.product.vendor'])
            ->get();
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    public function totalCents(): int
    {
        return $this->lines()->sum(fn (CartLine $line) => $line->lineTotalCents());
    }

    /**
     * @return array<int, array{vendor_id: int, vendor_name: string, subtotal_cents: int}>
     */
    public function vendorSubtotals(): array
    {
        return $this->lines()
            ->groupBy(fn (CartLine $line) => $line->variant->product->vendor_id)
            ->map(function (Collection $lines, int $vendorId) {
                $vendor = $lines->first()->variant->product->vendor;

                return [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor->store_name,
                    'subtotal_cents' => $lines->sum(fn (CartLine $line) => $line->lineTotalCents()),
                ];
            })
            ->values()
            ->all();
    }

    public function add(ProductVariant $variant, int $quantity = 1): void
    {
        $quantity = max(1, $quantity);

        if (! $variant->hasStock($quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock available.',
            ]);
        }

        $cart = $this->cart();

        $line = CartLine::query()->firstOrNew([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        $line->quantity = ($line->exists ? $line->quantity : 0) + $quantity;
        $line->save();
    }

    public function update(ProductVariant $variant, int $quantity): void
    {
        $cart = $this->cart();

        if ($quantity <= 0) {
            CartLine::query()
                ->where('cart_id', $cart->id)
                ->where('product_variant_id', $variant->id)
                ->delete();

            return;
        }

        if (! $variant->hasStock($quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock available.',
            ]);
        }

        CartLine::query()->updateOrCreate(
            ['cart_id' => $cart->id, 'product_variant_id' => $variant->id],
            ['quantity' => $quantity],
        );
    }

    public function remove(ProductVariant $variant): void
    {
        CartLine::query()
            ->where('cart_id', $this->cart()->id)
            ->where('product_variant_id', $variant->id)
            ->delete();
    }

    public function clear(): void
    {
        $this->cart()->lines()->delete();
    }

    public function mergeGuestCartOnLogin(): void
    {
        if (! auth()->check()) {
            return;
        }

        $sessionId = session()->getId();

        $guestCart = Cart::query()
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->where('status', 'active')
            ->first();

        if ($guestCart === null) {
            return;
        }

        $userCart = $this->cart();

        DB::transaction(function () use ($guestCart, $userCart): void {
            foreach ($guestCart->lines as $line) {
                $existing = CartLine::query()->firstOrNew([
                    'cart_id' => $userCart->id,
                    'product_variant_id' => $line->product_variant_id,
                ]);
                $existing->quantity = ($existing->exists ? $existing->quantity : 0) + $line->quantity;
                $existing->save();
            }

            $guestCart->lines()->delete();
            $guestCart->update(['status' => 'abandoned']);
        });
    }
}
