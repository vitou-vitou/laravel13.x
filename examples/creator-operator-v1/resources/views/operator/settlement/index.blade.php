<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="ops-page-title">Monthly settlement</h2>
                <p class="ops-page-subtitle">{{ '@'.$creator->handle }} · S÷T attribution</p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <a href="{{ route('operator.creators.settlement.create', $creator) }}" class="ops-link">Add period</a>
                <a href="{{ route('operator.creators.settlement.export', $creator) }}" class="ops-link-muted">Export CSV</a>
            </div>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 ops-stack">
            <x-flash />
            <x-creator-hub-nav :creator="$creator" />

            <x-ops-panel>
                <div class="overflow-x-auto">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Platform</th>
                                <th class="text-right">Attributed</th>
                                <th class="text-right">Commission</th>
                                <th class="text-right">Creator net</th>
                                <th>Payout</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($settlements as $row)
                                <tr>
                                    <td class="tabular-nums text-stone-500">{{ $row->period_start->toDateString() }} → {{ $row->period_end->toDateString() }}</td>
                                    <td>{{ $row->platform->label() }}</td>
                                    <td class="text-right tabular-nums">{{ $row->currency }} {{ number_format($row->attributed_revenue, 2) }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($row->commission_amount, 2) }}</td>
                                    <td class="text-right font-semibold tabular-nums">{{ number_format($row->creator_net, 2) }}</td>
                                    <td><span class="ops-tag">{{ $row->payout_status->label() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No settlement rows">
                                            Formula matches docs/creator-commission README.
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
