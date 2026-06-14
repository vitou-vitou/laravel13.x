<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Approval inbox — {{ '@'.$creator->handle }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <x-batch-loop-rail :current="5" />

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b font-medium">Pending your approval</div>
                @forelse ($pending as $entry)
                    <div class="px-4 py-4 border-b border-gray-100 last:border-0">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="font-medium">{{ $entry->title_variant ?? 'Untitled package' }}</div>
                                <div class="text-sm text-gray-500">{{ $entry->logged_on->toDateString() }}</div>
                                <a href="{{ $entry->tiktok_url }}" class="text-sm text-indigo-600 hover:underline" target="_blank" rel="noopener">TikTok source</a>
                                @if ($entry->notes)
                                    <p class="text-sm text-gray-600 mt-2">{{ $entry->notes }}</p>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('creator.approvals.approve', $entry) }}">
                                    @csrf
                                    <x-primary-button>Approve</x-primary-button>
                                </form>
                                <form method="POST" action="{{ route('creator.approvals.reject', $entry) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-md border border-stone-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-stone-700 shadow-sm hover:bg-stone-50">Skip</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-6 text-gray-500 text-sm">Nothing waiting — operator will notify you when the next batch is ready.</p>
                @endforelse
            </div>

            @if ($recent->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-4 py-3 border-b font-medium">Recent decisions</div>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recent as $entry)
                                <tr>
                                    <td class="px-4 py-2">{{ $entry->title_variant ?? '—' }}</td>
                                    <td class="px-4 py-2"><x-publish-status :status="$entry->status" /></td>
                                    <td class="px-4 py-2 text-gray-500">{{ $entry->updated_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
