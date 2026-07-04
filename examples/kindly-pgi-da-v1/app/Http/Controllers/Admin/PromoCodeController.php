<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PromoCodeType;
use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        return view('admin.promo-codes.index', [
            'promoCodes' => PromoCode::query()->with('vendor')->latest()->get(),
            'vendors' => Vendor::query()->orderBy('store_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:promo_codes,code'],
            'type' => ['required', Rule::enum(PromoCodeType::class)],
            'value' => ['required', 'integer', 'min:1'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'min_subtotal_cents' => ['nullable', 'integer', 'min:1'],
        ]);

        $type = $validated['type'] instanceof PromoCodeType
            ? $validated['type']
            : PromoCodeType::from($validated['type']);

        if ($type === PromoCodeType::Percent && (int) $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percent discount cannot exceed 100.'])->withInput();
        }

        PromoCode::query()->create([
            'code' => strtoupper($validated['code']),
            'type' => $type,
            'value' => (int) $validated['value'],
            'vendor_id' => $validated['vendor_id'] ?? null,
            'min_subtotal_cents' => $validated['min_subtotal_cents'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.promo-codes.index')->with('status', 'Promo code created.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->update(['is_active' => false]);

        return back()->with('status', 'Promo code deactivated.');
    }
}
