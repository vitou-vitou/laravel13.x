<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">TikTok import — {{ '@'.$creator->handle }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <x-creator-hub-nav :creator="$creator" />

            <p class="text-sm text-stone-600">Paste JSONL from <code class="text-xs bg-stone-100 px-1 rounded">tools/tiktok-metadata</code> (BUILD LIST step 1). Rows after <strong>last run</strong> and not already in publish log become candidates.</p>

            <form method="POST" action="{{ route('operator.creators.import.preview', $creator) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4 border border-stone-100">
                @csrf
                <div>
                    <x-input-label for="jsonl" value="JSONL paste" />
                    <textarea id="jsonl" name="jsonl" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm font-mono text-xs" required>{{ old('jsonl', $jsonl ?? '') }}</textarea>
                </div>
                <x-primary-button>Preview candidates</x-primary-button>
            </form>

            @isset($candidates)
                <div class="bg-white shadow-sm rounded-lg p-6 border border-stone-100 space-y-4">
                    <p class="text-sm text-stone-600">Parsed {{ $parsedCount ?? 0 }} line(s) · {{ $candidates->count() }} new candidate(s)</p>

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
