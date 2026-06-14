<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Batch queue
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <x-batch-loop-rail />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white shadow-sm rounded-lg p-4 border border-stone-100">
                    <div class="text-sm text-stone-500">Creators</div>
                    <div class="text-2xl font-semibold">{{ $creatorsCount }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border border-amber-100">
                    <div class="text-sm text-stone-500">Step 5 · Pending approval</div>
                    <div class="text-2xl font-semibold text-amber-600">{{ $pendingCount }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border border-sky-100">
                    <div class="text-sm text-stone-500">Step 6 · Ready to publish</div>
                    <div class="text-2xl font-semibold text-sky-600">{{ $approvedCount }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 border border-emerald-100">
                    <div class="text-sm text-stone-500">Published (7d)</div>
                    <div class="text-2xl font-semibold text-emerald-600">{{ $publishedThisWeek }}</div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('operator.creators.index') }}" class="inline-flex items-center rounded-md bg-stone-900 px-4 py-2 text-sm text-white">All creators</a>
                <a href="{{ route('operator.creators.create') }}" class="inline-flex items-center rounded-md border border-stone-300 bg-white px-4 py-2 text-sm text-stone-700">Onboard creator</a>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-stone-100">
                <div class="px-4 py-3 border-b border-stone-100 font-medium">Publish log · recent</div>
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Creator</th>
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Logged</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($recentEntries as $entry)
                            <tr>
                                <td class="px-4 py-2">{{ $entry->creator->handle }}</td>
                                <td class="px-4 py-2 truncate max-w-xs">{{ $entry->title_variant ?? '—' }}</td>
                                <td class="px-4 py-2"><x-publish-status :status="$entry->status" /></td>
                                <td class="px-4 py-2">{{ $entry->logged_on->toDateString() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-stone-500">No publish log rows yet — start at step 4 (packaging) on a creator hub.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
