<?php

namespace App\Http\Controllers\Operator;

use App\Enums\IntegrationEvent;
use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Services\ApprovalBatchNotifier;
use App\Services\IntegrationWebhookDispatcher;
use App\Services\TikTokThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

class PublishLogController extends Controller
{
    public function __construct(
        protected IntegrationWebhookDispatcher $webhookDispatcher,
        protected TikTokThumbnailService $thumbnailService,
        protected ApprovalBatchNotifier $batchNotifier,
    ) {}
    public function create(Creator $creator): View
    {
        $this->authorize('update', $creator);

        return view('operator.publish-log.create', compact('creator'));
    }

    public function store(Request $request, Creator $creator): RedirectResponse
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'logged_on' => ['required', 'date'],
            'tiktok_url' => ['required', 'url', 'max:500'],
            'title_variant' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $entry = PublishLogEntry::query()->create([
            ...$validated,
            'creator_id' => $creator->id,
            'status' => PublishStatus::PendingApproval,
        ]);

        $this->thumbnailService->hydrateEntry($entry);
        $this->batchNotifier->notifyIfNeeded($creator);

        return redirect()
            ->route('operator.creators.show', $creator)
            ->with('status', 'Publish log row added — pending creator approval.');
    }

    public function edit(Creator $creator, PublishLogEntry $entry): View
    {
        $this->authorize('update', $entry);
        abort_unless($entry->creator_id === $creator->id, 404);

        return view('operator.publish-log.edit', [
            'creator' => $creator,
            'entry' => $entry,
            'statuses' => PublishStatus::cases(),
        ]);
    }

    public function update(Request $request, Creator $creator, PublishLogEntry $entry): RedirectResponse
    {
        $this->authorize('update', $entry);
        abort_unless($entry->creator_id === $creator->id, 404);

        $validated = $request->validate([
            'logged_on' => ['required', 'date'],
            'tiktok_url' => ['required', 'url', 'max:500'],
            'title_variant' => ['nullable', 'string', 'max:500'],
            'yt_url' => ['nullable', 'url', 'max:500'],
            'ig_url' => ['nullable', 'url', 'max:500'],
            'yt_video_id' => ['nullable', 'string', 'max:100'],
            'posted_time' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(PublishStatus::class)],
            'views_yt_7d' => ['nullable', 'integer', 'min:0'],
            'views_ig_7d' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $entry->update($validated);

        return redirect()
            ->route('operator.creators.show', $creator)
            ->with('status', 'Publish log updated.');
    }

    public function publish(Request $request, Creator $creator, PublishLogEntry $entry): RedirectResponse
    {
        $this->authorize('publish', $entry);
        abort_unless($entry->creator_id === $creator->id, 404);

        $validated = $request->validate([
            'yt_url' => ['required', 'url', 'max:500'],
            'ig_url' => ['nullable', 'url', 'max:500'],
            'yt_video_id' => ['nullable', 'string', 'max:100'],
            'posted_time' => ['nullable', 'date'],
            'views_yt_7d' => ['nullable', 'integer', 'min:0'],
            'views_ig_7d' => ['nullable', 'integer', 'min:0'],
        ]);

        $entry->update([
            ...$validated,
            'posted_time' => $validated['posted_time'] ?? now(),
            'status' => PublishStatus::Published,
        ]);

        $creator->update(['last_run_date' => now()->toDateString()]);

        $entry->load('creator');
        $this->webhookDispatcher->dispatchForEntry(
            IntegrationEvent::PublishLogPublished,
            $entry,
        );

        return redirect()
            ->route('operator.creators.show', $creator)
            ->with('status', 'Marked published.');
    }
}
