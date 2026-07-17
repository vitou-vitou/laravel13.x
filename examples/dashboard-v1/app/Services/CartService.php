<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartService
{
    public function forUser(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function addItem(User $user, Product $product, int $quantity = 1): CartItem
    {
        $cart = $this->forUser($user);

        $item = CartItem::query()->firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + max(1, $quantity);
        $item->save();

        return $item;
    }

    public function updateQuantity(User $user, Product $product, int $quantity): ?CartItem
    {
        $cart = $this->forUser($user);

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item === null) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();

            return null;
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    public function clear(User $user): void
    {
        $this->forUser($user)->items()->delete();
    }

    public function totalCents(User $user): int
    {
        return $this->forUser($user)->totalCents();
    }
}
