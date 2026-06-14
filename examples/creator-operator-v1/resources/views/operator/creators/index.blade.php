<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Creators</h2>
            <a href="{{ route('operator.creators.create') }}" class="text-sm text-indigo-600 hover:underline">Onboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Handle</th>
                            <th class="px-4 py-2 text-left">Tier</th>
                            <th class="px-4 py-2 text-left">Pending</th>
                            <th class="px-4 py-2 text-left">Last run</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($creators as $creator)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $creator->handle }}</td>
                                <td class="px-4 py-2">{{ $creator->tier->label() }}</td>
                                <td class="px-4 py-2">{{ $creator->pending_count }}</td>
                                <td class="px-4 py-2">{{ $creator->last_run_date?->toDateString() ?? '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('operator.creators.show', $creator) }}" class="text-indigo-600 hover:underline">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-gray-500">No creators yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
