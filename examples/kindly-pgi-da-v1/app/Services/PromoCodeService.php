<?php

namespace App\Services;

use App\Enums\PromoCodeType;
use App\Models\CartLine;
use App\Models\PromoCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class PromoCodeService
{
    public const SESSION_KEY = 'cart_promo_code_id';

    public function findByCode(string $code): ?PromoCode
    {
        return PromoCode::query()
            ->where('code', strtoupper(trim($code)))
            ->first();
    }

    public function appliedPromo(): ?PromoCode
    {
        $id = Session::get(self::SESSION_KEY);

        if (! $id) {
            return null;
        }

        $promo = PromoCode::query()->find($id);

        if ($promo === null || ! $promo->isUsable()) {
            Session::forget(self::SESSION_KEY);

            return null;
        }

        return $promo;
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function applyToSession(string $code, int $subtotalCents, Collection $lines): PromoCode
    {
        $promo = $this->findByCode($code);

        if ($promo === null) {
            throw ValidationException::withMessages([
                'code' => 'This promo code is not valid.',
            ]);
        }

        $this->assertUsableForSubtotal($promo, $subtotalCents, $lines);

        Session::put(self::SESSION_KEY, $promo->id);

        return $promo;
    }

    public function clearSession(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function discountCents(PromoCode $promo, int $subtotalCents, Collection $lines): int
    {
        $applicable = $this->applicableSubtotalCents($promo, $subtotalCents, $lines);

        if ($applicable <= 0) {
            return 0;
        }

        $discount = match ($promo->type) {
            PromoCodeType::Percent => (int) round($applicable * $promo->value / 100),
            PromoCodeType::Fixed => $promo->value,
        };

        return min($discount, $applicable, $subtotalCents);
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function assertUsableForSubtotal(PromoCode $promo, int $subtotalCents, Collection $lines): void
    {
        if (! $promo->isUsable()) {
            throw ValidationException::withMessages([
                'code' => 'This promo code is no longer available.',
            ]);
        }

        if ($subtotalCents <= 0) {
            throw ValidationException::withMessages([
                'code' => 'Add items to your cart before applying a promo code.',
            ]);
        }

        $applicable = $this->applicableSubtotalCents($promo, $subtotalCents, $lines);

        if ($promo->vendor_id !== null && $applicable <= 0) {
            throw ValidationException::withMessages([
                'code' => 'This promo code only applies to items from that vendor.',
            ]);
        }

        if ($promo->min_subtotal_cents !== null && $applicable < $promo->min_subtotal_cents) {
            throw ValidationException::withMessages([
                'code' => 'Cart subtotal for this promo must be at least $'.number_format($promo->min_subtotal_cents / 100, 2).'.',
            ]);
        }

        if ($this->discountCents($promo, $subtotalCents, $lines) <= 0) {
            throw ValidationException::withMessages([
                'code' => 'This promo code does not apply to your cart.',
            ]);
        }
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function applicableSubtotalCents(PromoCode $promo, int $subtotalCents, Collection $lines): int
    {
        if ($promo->vendor_id === null) {
            return $subtotalCents;
        }

        return (int) $lines
            ->filter(fn (CartLine $line) => $line->variant->product->vendor_id === $promo->vendor_id)
            ->sum(fn (CartLine $line) => $line->lineTotalCents());
    }

    public function recordUse(?PromoCode $promo): void
    {
        if ($promo === null) {
            return;
        }

        $promo->increment('uses_count');
    }
}
