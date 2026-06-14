<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Weekly reports</h2>
            <p class="ops-page-subtitle">Read-only metrics for {{ '@'.$creator->handle }}</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 ops-stack">
            <p class="text-sm text-stone-600">Operator-recorded weekly batch summaries. Numbers match what your operator logged after step 7.</p>

            <x-ops-panel>
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Videos</th>
                            <th>Best</th>
                            <th>Experiment</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($metrics as $metric)
                            <tr>
                                <td class="tabular-nums">{{ $metric->week_start->toDateString() }}</td>
                                <td>{{ $metric->videos_published }}</td>
                                <td class="tabular-nums">{{ number_format($metric->best_video_views ?? 0) }}</td>
                                <td>{{ $metric->experiment ?? '—' }}</td>
                                <td>{{ $metric->experiment_result ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="No metrics published yet">
                                        Your operator will share weekly reports after each batch.
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
