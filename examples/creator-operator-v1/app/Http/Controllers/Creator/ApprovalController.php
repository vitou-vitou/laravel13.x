<?php

namespace App\Http\Controllers\Creator;

use App\Enums\IntegrationEvent;
use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\PublishLogEntry;
use App\Services\IntegrationWebhookDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(
        protected IntegrationWebhookDispatcher $webhookDispatcher,
    ) {}
    public function index(Request $request): View
    {
        $creator = $request->user()->creatorProfile;

        abort_if($creator === null, 403, 'No creator profile linked to this account.');

        $pending = $creator->publishLogEntries()
            ->where('status', PublishStatus::PendingApproval)
            ->latest('logged_on')
            ->get();

        $recent = $creator->publishLogEntries()
            ->whereIn('status', [
                PublishStatus::Approved,
                PublishStatus::Published,
                PublishStatus::SkippedCreator,
            ])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('creator.approvals.index', compact('creator', 'pending', 'recent'));
    }

    public function approve(Request $request, PublishLogEntry $entry): RedirectResponse
    {
        $this->authorize('approve', $entry);

        $entry->update([
            'status' => PublishStatus::Approved,
            'approved_at' => now(),
            'approved_by_user_id' => $request->user()->id,
        ]);

        $entry->load('creator');
        $this->webhookDispatcher->dispatchForEntry(
            IntegrationEvent::PublishLogApproved,
            $entry,
        );

        return back()->with('status', 'Approved — operator can publish.');
    }

    public function reject(Request $request, PublishLogEntry $entry): RedirectResponse
    {
        $this->authorize('reject', $entry);

        $entry->update([
            'status' => PublishStatus::SkippedCreator,
            'approved_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Skipped — creator rejected.');
    }
}
