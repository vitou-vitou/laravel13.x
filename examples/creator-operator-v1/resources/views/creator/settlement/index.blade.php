<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Settlement statement</h2>
            <p class="ops-page-subtitle">Monthly attribution using the S÷T formula</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 ops-stack">
            <p class="text-sm text-stone-600">These numbers use the same formula as your operator's records. Dispute window: 14 days per commission spec.</p>

            <x-ops-panel>
                <div class="overflow-x-auto">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Platform</th>
                                <th class="text-right">Attributed</th>
                                <th class="text-right">Commission</th>
                                <th class="text-right">Your net</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($settlements as $row)
                                <tr>
                                    <td class="tabular-nums text-stone-500">{{ $row->period_start->toDateString() }} → {{ $row->period_end->toDateString() }}</td>
                                    <td>{{ $row->platform->label() }}</td>
                                    <td class="text-right tabular-nums">{{ $row->currency }} {{ number_format($row->attributed_revenue, 2) }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->commission_amount, 2) }}</td>
                                    <td class="text-right font-semibold text-emerald-700 tabular-nums">{{ number_format($row->creator_net, 2) }}</td>
                                    <td><span class="ops-tag">{{ $row->payout_status->label() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No settlement periods yet">
                                            Monthly statements appear after your operator closes a period.
                                        </x-empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
