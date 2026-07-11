<?php

namespace App\Http\Controllers;

use App\Enums\DisputeStatus;
use App\Enums\OrderGroupStatus;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\OrderGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DisputeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function store(Request $request, OrderGroup $orderGroup): RedirectResponse
    {
        abort_unless($orderGroup->order->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        if ($orderGroup->dispute) {
            return back()->with('status', 'A dispute already exists for this shipment.');
        }

        $dispute = Dispute::query()->create([
            'order_group_id' => $orderGroup->id,
            'user_id' => auth()->id(),
            'status' => DisputeStatus::Opened,
            'reason' => $validated['reason'],
        ]);

        $orderGroup->update(['status' => OrderGroupStatus::Disputed]);

        DisputeMessage::query()->create([
            'dispute_id' => $dispute->id,
            'user_id' => auth()->id(),
            'body' => $validated['reason'],
        ]);

        return redirect()
            ->route('disputes.show', $dispute)
            ->with('status', 'Dispute filed.');
    }

    public function show(Dispute $dispute): View
    {
        $user = auth()->user();
        abort_unless(
            $dispute->user_id === $user->id
            || $user->isAdmin()
            || ($user->vendor && $dispute->orderGroup->vendor_id === $user->vendor->id),
            403,
        );

        $dispute->load(['messages.user', 'orderGroup.vendor', 'orderGroup.order']);

        return view('disputes.show', compact('dispute'));
    }

    public function message(Request $request, Dispute $dispute): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(
            $dispute->user_id === $user->id
            || $user->isAdmin()
            || ($user->vendor && $dispute->orderGroup->vendor_id === $user->vendor->id),
            403,
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        DisputeMessage::query()->create([
            'dispute_id' => $dispute->id,
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        return back()->with('status', 'Message posted.');
    }
}
