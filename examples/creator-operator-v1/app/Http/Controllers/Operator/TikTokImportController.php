<?php

namespace App\Http\Controllers\Operator;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Services\TikTokMetadataImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TikTokImportController extends Controller
{
    public function __construct(
        protected TikTokMetadataImportService $importService,
    ) {}

    public function index(Creator $creator): View
    {
        $this->authorize('update', $creator);

        return view('operator.import.index', compact('creator'));
    }

    public function preview(Request $request, Creator $creator): View
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'jsonl' => ['required', 'string', 'max:500000'],
        ]);

        $parsed = $this->importService->parseJsonl($validated['jsonl']);
        $candidates = $this->importService->candidatesForCreator($creator, $parsed);

        return view('operator.import.index', [
            'creator' => $creator,
            'jsonl' => $validated['jsonl'],
            'candidates' => $candidates,
            'parsedCount' => count($parsed),
        ]);
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

            $creator->publishLogEntries()->create([
                'logged_on' => now()->toDateString(),
                'tiktok_url' => $url,
                'title_variant' => $validated['titles'][$url] ?? null,
                'status' => PublishStatus::PendingApproval,
                'notes' => 'Imported from TikTok metadata JSONL (BUILD LIST).',
            ]);

            $created++;
        }

        return redirect()
            ->route('operator.creators.show', $creator)
            ->with('status', "{$created} publish log row(s) added from import.");
    }
}
