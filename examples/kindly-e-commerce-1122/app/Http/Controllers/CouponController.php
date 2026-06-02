<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private CouponService $coupons) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $this->coupons->apply($validated['code']);

        return redirect()
            ->route('cart.index')
            ->with('status', 'Coupon applied.');
    }

    public function destroy(): RedirectResponse
    {
        $this->coupons->clear();

        return redirect()
            ->route('cart.index')
            ->with('status', 'Coupon removed.');
    }
}
