<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Weekly reports</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-stone-600">Read-only view of operator-recorded weekly metrics for {{ '@'.$creator->handle }}.</p>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-stone-100">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Week</th>
                            <th class="px-4 py-2 text-left">Videos</th>
                            <th class="px-4 py-2 text-left">Best</th>
                            <th class="px-4 py-2 text-left">Experiment</th>
                            <th class="px-4 py-2 text-left">Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($metrics as $metric)
                            <tr>
                                <td class="px-4 py-2">{{ $metric->week_start->toDateString() }}</td>
                                <td class="px-4 py-2">{{ $metric->videos_published }}</td>
                                <td class="px-4 py-2">{{ number_format($metric->best_video_views ?? 0) }}</td>
                                <td class="px-4 py-2">{{ $metric->experiment ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $metric->experiment_result ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-stone-500">No metrics published yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
