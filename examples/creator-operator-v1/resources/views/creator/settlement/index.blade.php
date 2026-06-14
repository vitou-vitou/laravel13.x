<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settlement statement</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-stone-600">Monthly settlement rows use the S÷T attribution formula from the commission README.</p>

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto border border-stone-100">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Period</th>
                            <th class="px-4 py-2 text-left">Platform</th>
                            <th class="px-4 py-2 text-right">Attributed</th>
                            <th class="px-4 py-2 text-right">Commission</th>
                            <th class="px-4 py-2 text-right">Your net</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($settlements as $row)
                            <tr>
                                <td class="px-4 py-2">{{ $row->period_start->toDateString() }} → {{ $row->period_end->toDateString() }}</td>
                                <td class="px-4 py-2">{{ $row->platform->label() }}</td>
                                <td class="px-4 py-2 text-right">{{ $row->currency }} {{ number_format($row->attributed_revenue, 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($row->commission_amount, 2) }}</td>
                                <td class="px-4 py-2 text-right font-medium text-emerald-700">{{ number_format($row->creator_net, 2) }}</td>
                                <td class="px-4 py-2">
                                    <span class="text-xs rounded-full px-2 py-0.5 bg-stone-100">{{ $row->payout_status->label() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-stone-500">No settlement periods yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
