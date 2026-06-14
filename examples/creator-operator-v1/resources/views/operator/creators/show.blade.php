<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ '@'.$creator->handle }}</h2>
            <div class="flex flex-wrap gap-3 text-sm">
                <a href="{{ route('operator.creators.publish-log.create', $creator) }}" class="text-indigo-600 hover:underline">Add publish row (step 4)</a>
                <a href="{{ route('operator.creators.publish-log.export', $creator) }}" class="text-stone-600 hover:underline">Export CSV</a>
                <a href="{{ route('operator.creators.edit', $creator) }}" class="text-stone-600 hover:underline">Onboarding</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <x-creator-hub-nav :creator="$creator" />

            <x-batch-loop-rail :current="4" />

            <div class="bg-white shadow-sm rounded-lg p-4 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm border border-stone-100">
                <div><span class="text-stone-500">Tier</span><div class="font-medium">{{ $creator->tier->label() }}</div></div>
                <div><span class="text-stone-500">Music policy</span><div class="font-medium">{{ $creator->music_policy->label() }}</div></div>
                <div><span class="text-stone-500">Last run (BUILD LIST)</span><div class="font-medium">{{ $creator->last_run_date?->toDateString() ?? '—' }}</div></div>
                <div><span class="text-stone-500">Pending approval</span><div class="font-medium text-amber-700">{{ $statusCounts['pending_approval'] ?? 0 }}</div></div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('operator.creators.show', $creator) }}"
                   @class(['rounded-full px-3 py-1 text-xs font-medium border', 'bg-stone-900 text-white border-stone-900' => ! $statusFilter, 'bg-white text-stone-700 border-stone-300 hover:bg-stone-50' => $statusFilter])>
                    All
                </a>
                @foreach ($statuses as $status)
                    <a href="{{ route('operator.creators.show', [$creator, 'status' => $status->value]) }}"
                       @class(['rounded-full px-3 py-1 text-xs font-medium border', 'bg-stone-900 text-white border-stone-900' => $statusFilter === $status->value, 'bg-white text-stone-700 border-stone-300 hover:bg-stone-50' => $statusFilter !== $status->value])>
                        {{ $status->label() }}
                        @if (($statusCounts[$status->value] ?? 0) > 0)
                            <span class="opacity-70">({{ $statusCounts[$status->value] }})</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-stone-100">
                <div class="px-4 py-3 border-b border-stone-100 font-medium">Publish log tab</div>
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($entries as $entry)
                            <tr>
                                <td class="px-4 py-2">{{ $entry->logged_on->toDateString() }}</td>
                                <td class="px-4 py-2 max-w-xs truncate">{{ $entry->title_variant ?? '—' }}</td>
                                <td class="px-4 py-2"><x-publish-status :status="$entry->status" /></td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('operator.creators.publish-log.edit', [$creator, $entry]) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    @if ($entry->status->isPublishable())
                                        <a href="{{ route('operator.creators.publish-log.edit', [$creator, $entry]) }}#publish" class="text-emerald-600 hover:underline">Publish</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-stone-500">No rows — add from weekly batch step 4 (packaging).</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
