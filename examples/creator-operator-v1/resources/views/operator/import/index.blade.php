<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">TikTok import — {{ '@'.$creator->handle }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->has('cli'))
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $errors->first('cli') }}</div>
            @endif

            <x-creator-hub-nav :creator="$creator" />

            <x-batch-loop-rail :current="1" />

            <p class="text-sm text-stone-600">BUILD LIST step 1 — fetch metadata from <code class="text-xs bg-stone-100 px-1 rounded">tools/tiktok-metadata</code> or paste JSONL. Rows after <strong>last run</strong> ({{ $creator->last_run_date?->toDateString() ?? 'not set' }}) that are not already in the publish log become candidates.</p>

            @if ($cliConfigured ?? false)
                <div class="bg-white shadow-sm rounded-lg p-6 space-y-4 border border-stone-100">
                    <h3 class="font-medium text-stone-900">Run CLI (profile {{ '@'.$creator->handle }})</h3>
                    <p class="text-sm text-stone-500">Calls <code class="text-xs">scrape_tiktok.py --metadata-only</code> with this creator&apos;s handle and last-run date. Requires Python + yt-dlp on the server.</p>
                    <form method="POST" action="{{ route('operator.creators.import.cli', $creator) }}" class="flex flex-wrap items-end gap-4">
                        @csrf
                        <div>
                            <x-input-label for="limit" value="Max videos (optional)" />
                            <x-text-input id="limit" name="limit" type="number" min="1" max="100" class="mt-1 w-32" value="{{ old('limit', 20) }}" />
                        </div>
                        <x-primary-button>Fetch from TikTok</x-primary-button>
                    </form>
                    @if (($importSource ?? null) === 'cli')
                        <p class="text-sm text-green-700">CLI fetch completed — preview below.</p>
                    @endif
                </div>
            @else
                <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm text-amber-900">
                    CLI not configured — set <code class="text-xs">TIKTOK_METADATA_SCRIPT</code> or install repo <code class="text-xs">tools/tiktok-metadata</code>. Use JSONL paste below.
                </div>
            @endif

            <form method="POST" action="{{ route('operator.creators.import.preview', $creator) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4 border border-stone-100">
                @csrf
                <h3 class="font-medium text-stone-900">Paste JSONL</h3>
                <div>
                    <x-input-label for="jsonl" value="JSONL content" />
                    <textarea id="jsonl" name="jsonl" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm font-mono text-xs" required>{{ old('jsonl', $jsonl ?? '') }}</textarea>
                </div>
                <x-primary-button>Preview candidates</x-primary-button>
            </form>

            @isset($candidates)
                <div class="bg-white shadow-sm rounded-lg p-6 border border-stone-100 space-y-4">
                    <p class="text-sm text-stone-600">
                        Parsed {{ $parsedCount ?? 0 }} line(s) · {{ $candidates->count() }} new candidate(s)
                        @if (! empty($importSource))
                            · source: {{ $importSource }}
                        @endif
                    </p>

                    @if ($candidates->isEmpty())
                        <p class="text-stone-500 text-sm">No new URLs to import (deduped or filtered by last run date).</p>
                    @else
                        <form method="POST" action="{{ route('operator.creators.import.store', $creator) }}" class="space-y-3">
                            @csrf
                            @foreach ($candidates as $index => $row)
                                <label class="flex items-start gap-3 p-3 rounded border border-stone-200 hover:bg-stone-50">
                                    <input type="checkbox" name="selected_urls[]" value="{{ $row['video_url'] }}" checked class="mt-1 rounded border-gray-300">
                                    <span class="text-sm">
                                        <span class="font-medium block truncate">{{ $row['video_url'] }}</span>
                                        <span class="text-stone-500">{{ Str::limit($row['caption'] ?? 'No caption', 80) }}</span>
                                        @if (! empty($row['music_title']))
                                            <span class="text-amber-700 text-xs block">Music: {{ Str::limit($row['music_title'], 60) }}</span>
                                        @endif
                                        <input type="hidden" name="titles[{{ $row['video_url'] }}]" value="{{ $row['caption'] ?? '' }}">
                                    </span>
                                </label>
                            @endforeach
                            <x-primary-button>Import selected</x-primary-button>
                        </form>
                    @endif
                </div>
            @endisset
        </div>
    </div>
</x-app-layout>
