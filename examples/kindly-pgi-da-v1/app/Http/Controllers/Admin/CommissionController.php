<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CommissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function edit(CommissionService $commission): View
    {
        return view('admin.commission.edit', [
            'bps' => $commission->defaultBps(),
        ]);
    }

    public function update(Request $request, CommissionService $commission): RedirectResponse
    {
        $validated = $request->validate([
            'default_commission_bps' => ['required', 'integer', 'min:0', 'max:5000'],
        ]);

        $commission->updateDefaultBps((int) $validated['default_commission_bps']);

        return back()->with('status', 'Commission rate updated.');
    }
}
