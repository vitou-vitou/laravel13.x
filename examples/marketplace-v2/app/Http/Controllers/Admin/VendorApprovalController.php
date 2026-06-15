<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorApprovalController extends Controller
{
    public function index(): View
    {
        $vendors = Vendor::query()
            ->withoutGlobalScopes()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function approve(Vendor $vendor): RedirectResponse
    {
        $vendor = Vendor::query()->withoutGlobalScopes()->findOrFail($vendor->id);
        $vendor->update(['status' => VendorStatus::Active]);

        return back()->with('status', 'Vendor approved.');
    }
}
