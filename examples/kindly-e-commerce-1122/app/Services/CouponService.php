<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CouponService
{
    private const SESSION_KEY = 'applied_coupon_code';

    public function appliedCode(): ?string
    {
        $code = Session::get(self::SESSION_KEY);

        return is_string($code) && $code !== '' ? $code : null;
    }

    public function apply(string $code): Coupon
    {
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();

        if ($coupon === null) {
            throw ValidationException::withMessages([
                'code' => 'This coupon code is invalid.',
            ]);
        }

        Session::put(self::SESSION_KEY, $coupon->code);

        return $coupon;
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function resolveForSubtotal(int $subtotalCents): ?Coupon
    {
        $code = $this->appliedCode();

        if ($code === null) {
            return null;
        }

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($coupon === null) {
            $this->clear();

            return null;
        }

        return $coupon;
    }

    public function discountCents(int $subtotalCents): int
    {
        $coupon = $this->resolveForSubtotal($subtotalCents);

        return $coupon?->discountCentsFor($subtotalCents) ?? 0;
    }
}
