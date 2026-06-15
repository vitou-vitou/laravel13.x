<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VendorRegistrationController extends Controller
{
    public function create(): View
    {
        return view('vendor.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
        ]);

        $user = $request->user();

        if ($user->vendor) {
            return redirect()->route('vendor.dashboard');
        }

        $user->update(['role' => UserRole::Vendor]);

        Vendor::query()->create([
            'user_id' => $user->id,
            'store_name' => $validated['store_name'],
            'slug' => Str::slug($validated['store_name']).'-'.$user->id,
            'status' => VendorStatus::Pending,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Vendor application submitted. Awaiting admin approval.');
    }
}
