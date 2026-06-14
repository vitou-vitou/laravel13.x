<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="ops-page-title">Weekly metrics</h2>
                <p class="ops-page-subtitle">{{ '@'.$creator->handle }} · step 7 REPORT</p>
            </div>
            <a href="{{ route('operator.creators.metrics.create', $creator) }}" class="ops-btn-primary ops-btn-sm">Add week</a>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 ops-stack">
            <x-flash />
            <x-creator-hub-nav :creator="$creator" />

            <x-ops-panel>
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th>Week start</th>
                            <th>Videos</th>
                            <th>Best video</th>
                            <th>Experiment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($metrics as $metric)
                            <tr>
                                <td class="tabular-nums">{{ $metric->week_start->toDateString() }}</td>
                                <td>{{ $metric->videos_published }}</td>
                                <td>
                                    @if ($metric->best_video_url)
                                        <a href="{{ $metric->best_video_url }}" class="ops-link" target="_blank" rel="noopener">{{ number_format($metric->best_video_views ?? 0) }} views</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="max-w-xs truncate">{{ $metric->experiment ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="No weekly metrics yet">
                                        Add after batch step 7 (REPORT).
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
