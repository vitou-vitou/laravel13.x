<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Weekly metrics — {{ '@'.$creator->handle }}</h2>
            <a href="{{ route('operator.creators.metrics.create', $creator) }}" class="text-sm text-indigo-600 hover:underline">Add week (step 7)</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <x-creator-hub-nav :creator="$creator" />

            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-stone-100">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Week start</th>
                            <th class="px-4 py-2 text-left">Videos</th>
                            <th class="px-4 py-2 text-left">Best video</th>
                            <th class="px-4 py-2 text-left">Experiment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($metrics as $metric)
                            <tr>
                                <td class="px-4 py-2">{{ $metric->week_start->toDateString() }}</td>
                                <td class="px-4 py-2">{{ $metric->videos_published }}</td>
                                <td class="px-4 py-2">
                                    @if ($metric->best_video_url)
                                        <a href="{{ $metric->best_video_url }}" class="text-indigo-600 hover:underline" target="_blank" rel="noopener">{{ number_format($metric->best_video_views ?? 0) }} views</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 max-w-xs truncate">{{ $metric->experiment ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-stone-500">No weekly metrics yet — add after batch step 7 (REPORT).</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
