<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DisputeStatus;
use App\Enums\OrderGroupStatus;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function index(): View
    {
        $disputes = Dispute::query()
            ->with(['orderGroup.vendor', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function resolve(Request $request, Dispute $dispute, PayoutService $payouts): RedirectResponse
    {
        $validated = $request->validate([
            'resolution' => ['required', 'in:buyer,vendor'],
        ]);

        $status = $validated['resolution'] === 'buyer'
            ? DisputeStatus::ResolvedBuyer
            : DisputeStatus::ResolvedVendor;

        $dispute->update(['status' => $status]);

        $group = $dispute->orderGroup;
        $group->update(['status' => OrderGroupStatus::Completed]);

        if ($group->payout && $validated['resolution'] === 'vendor') {
            $payouts->release($group->payout);
        }

        return back()->with('status', 'Dispute resolved.');
    }
}
