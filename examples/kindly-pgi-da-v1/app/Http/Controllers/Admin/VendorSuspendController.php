<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;

class VendorSuspendController extends Controller
{
    public function suspend(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['status' => VendorStatus::Suspended]);

        return back()->with('status', 'Vendor suspended.');
    }

    public function activate(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['status' => VendorStatus::Active]);

        return back()->with('status', 'Vendor reactivated.');
    }
}
