<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">TikTok import</h2>
            <p class="ops-page-subtitle">{{ '@'.$creator->handle }} · BUILD LIST step 1</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-narrow ops-stack">
            <x-flash />

            @if ($errors->has('cli'))
                <div class="ops-flash-error">{{ $errors->first('cli') }}</div>
            @endif

            <x-creator-hub-nav :creator="$creator" />

            <x-batch-loop-rail :current="1" />

            <p class="text-sm text-stone-600 leading-relaxed">
                Fetch metadata from <code class="text-xs rounded bg-stone-100 px-1.5 py-0.5 font-mono">tools/tiktok-metadata</code> or paste JSONL.
                Rows after <strong>last run</strong> ({{ $creator->last_run_date?->toDateString() ?? 'not set' }}) that are not already in the publish log become candidates.
            </p>

            @if ($cliConfigured ?? false)
                <x-ops-panel title="Run CLI">
                    <p class="text-sm text-stone-500 mb-4">Calls <code class="text-xs font-mono">scrape_tiktok.py --metadata-only</code> for {{ '@'.$creator->handle }}. Requires Python + yt-dlp on the server.</p>
                    <form method="POST" action="{{ route('operator.creators.import.cli', $creator) }}" class="flex flex-wrap items-end gap-4">
                        @csrf
                        <div>
                            <x-input-label for="limit" value="Max videos (optional)" />
                            <x-text-input id="limit" name="limit" type="number" min="1" max="100" class="mt-1 w-32" value="{{ old('limit', 20) }}" />
                        </div>
                        <x-primary-button>Fetch from TikTok</x-primary-button>
                    </form>
                    @if (($importSource ?? null) === 'cli')
                        <p class="text-sm text-emerald-700 mt-4">CLI fetch completed — preview below.</p>
                    @endif
                </x-ops-panel>
            @else
                <div class="ops-flash-warn">
                    CLI not configured — set <code class="text-xs">TIKTOK_METADATA_SCRIPT</code> or install repo <code class="text-xs">tools/tiktok-metadata</code>. Use JSONL paste below.
                </div>
            @endif

            <x-ops-panel title="Paste JSONL">
                <form method="POST" action="{{ route('operator.creators.import.preview', $creator) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="jsonl" value="JSONL content" />
                        <textarea id="jsonl" name="jsonl" rows="8" class="mt-1 ops-textarea" required>{{ old('jsonl', $jsonl ?? '') }}</textarea>
                    </div>
                    <x-primary-button>Preview candidates</x-primary-button>
                </form>
            </x-ops-panel>

            @isset($candidates)
                <x-ops-panel title="Import preview">
                    <p class="text-sm text-stone-600 mb-4">
                        Parsed {{ $parsedCount ?? 0 }} line(s) · {{ $candidates->count() }} new candidate(s)
                        @if (! empty($importSource))
                            · source: {{ $importSource }}
                        @endif
                    </p>

                    @if ($candidates->isEmpty())
                        <x-empty-state title="No new URLs">
                            Deduped or filtered by last run date.
                        </x-empty-state>
                    @else
                        <form method="POST" action="{{ route('operator.creators.import.store', $creator) }}" class="space-y-3">
                            @csrf
                            @foreach ($candidates as $index => $row)
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-stone-200 hover:bg-stone-50 cursor-pointer transition-colors">
                                    <input type="checkbox" name="selected_urls[]" value="{{ $row['video_url'] }}" checked class="mt-1 rounded border-stone-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm min-w-0">
                                        <span class="font-medium block truncate">{{ $row['video_url'] }}</span>
                                        <span class="text-stone-500">{{ Str::limit($row['caption'] ?? 'No caption', 80) }}</span>
                                        @if (! empty($row['music_title']))
                                            <span class="text-amber-700 text-xs block mt-0.5">Music: {{ Str::limit($row['music_title'], 60) }}</span>
                                        @endif
                                        <input type="hidden" name="titles[{{ $row['video_url'] }}]" value="{{ $row['caption'] ?? '' }}">
                                    </span>
                                </label>
                            @endforeach
                            <x-primary-button>Import selected</x-primary-button>
                        </form>
                    @endif
                </x-ops-panel>
            @endisset
        </div>
    </div>
</x-app-layout>
