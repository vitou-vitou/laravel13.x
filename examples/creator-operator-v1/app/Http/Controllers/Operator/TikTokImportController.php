<?php

namespace App\Http\Controllers\Operator;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Services\ApprovalBatchNotifier;
use App\Services\TikTokMetadataCliRunner;
use App\Services\TikTokMetadataImportService;
use App\Services\TikTokThumbnailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class TikTokImportController extends Controller
{
    public function __construct(
        protected TikTokMetadataImportService $importService,
        protected TikTokMetadataCliRunner $cliRunner,
        protected TikTokThumbnailService $thumbnailService,
        protected ApprovalBatchNotifier $batchNotifier,
    ) {}

    public function index(Creator $creator): View
    {
        $this->authorize('update', $creator);

        return view('operator.import.index', [
            'creator' => $creator,
            'cliConfigured' => $this->cliRunner->isConfigured(),
        ]);
    }

    public function preview(Request $request, Creator $creator): View
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'jsonl' => ['required', 'string', 'max:500000'],
        ]);

        return $this->previewFromJsonl($creator, $validated['jsonl'], source: 'paste');
    }

    public function fetchCli(Request $request, Creator $creator): View|RedirectResponse
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            $jsonl = $this->cliRunner->fetchJsonl($creator, $validated['limit'] ?? null);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('operator.creators.import.index', $creator)
                ->withErrors(['cli' => $exception->getMessage()]);
        }

        return $this->previewFromJsonl($creator, $jsonl, source: 'cli', cliConfigured: true);
    }

    public function store(Request $request, Creator $creator): RedirectResponse
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'selected_urls' => ['required', 'array', 'min:1'],
            'selected_urls.*' => ['required', 'url', 'max:500'],
            'titles' => ['nullable', 'array'],
            'titles.*' => ['nullable', 'string', 'max:500'],
        ]);

        $created = 0;

        foreach ($validated['selected_urls'] as $url) {
            $exists = $creator->publishLogEntries()
                ->where('tiktok_url', $url)
                ->exists();

            if ($exists) {
                continue;
            }

            $entry = $creator->publishLogEntries()->create([
                'logged_on' => now()->toDateString(),
                'tiktok_url' => $url,
                'title_variant' => $validated['titles'][$url] ?? null,
                'status' => PublishStatus::PendingApproval,
                'notes' => 'Imported from TikTok metadata (BUILD LIST).',
            ]);

            $this->thumbnailService->hydrateEntry($entry);
            $created++;
        }

        if ($created > 0) {
            $this->batchNotifier->notifyIfNeeded($creator, $created);
        }

        return redirect()
            ->route('operator.creators.show', $creator)
            ->with('status', "{$created} publish log row(s) added from import.");
    }

    private function previewFromJsonl(Creator $creator, string $jsonl, string $source, bool $cliConfigured = false): View
    {
        $parsed = $this->importService->parseJsonl($jsonl);
        $candidates = $this->importService->candidatesForCreator($creator, $parsed);

        return view('operator.import.index', [
            'creator' => $creator,
            'jsonl' => $jsonl,
            'candidates' => $candidates,
            'parsedCount' => count($parsed),
            'importSource' => $source,
            'cliConfigured' => $cliConfigured || $this->cliRunner->isConfigured(),
        ]);
    }
}
