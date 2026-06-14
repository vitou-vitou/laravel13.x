<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Monthly settlement — {{ '@'.$creator->handle }}</h2>
            <a href="{{ route('operator.creators.settlement.create', $creator) }}" class="text-sm text-indigo-600 hover:underline">Add period</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <x-creator-hub-nav :creator="$creator" />

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto border border-stone-100">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Period</th>
                            <th class="px-4 py-2 text-left">Platform</th>
                            <th class="px-4 py-2 text-right">Attributed</th>
                            <th class="px-4 py-2 text-right">Commission</th>
                            <th class="px-4 py-2 text-right">Creator net</th>
                            <th class="px-4 py-2 text-left">Payout</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($settlements as $row)
                            <tr>
                                <td class="px-4 py-2">{{ $row->period_start->toDateString() }} → {{ $row->period_end->toDateString() }}</td>
                                <td class="px-4 py-2">{{ $row->platform->label() }}</td>
                                <td class="px-4 py-2 text-right">{{ $row->currency }} {{ number_format($row->attributed_revenue, 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($row->commission_amount, 2) }}</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($row->creator_net, 2) }}</td>
                                <td class="px-4 py-2">{{ $row->payout_status->label() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-stone-500">No settlement rows — formula matches docs/creator-commission README.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
