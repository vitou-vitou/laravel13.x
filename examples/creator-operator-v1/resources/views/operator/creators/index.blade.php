<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="ops-page-title">Creators</h2>
                <p class="ops-page-subtitle">Roster, tiers, and pending approval counts</p>
            </div>
            <a href="{{ route('operator.creators.create') }}" class="ops-btn-primary ops-btn-sm">Onboard</a>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container">
            <x-flash />

            <x-ops-panel>
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th>Handle</th>
                            <th>Tier</th>
                            <th>Pending</th>
                            <th>Last run</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($creators as $creator)
                            <tr>
                                <td class="font-medium">{{ $creator->handle }}</td>
                                <td>{{ $creator->tier->label() }}</td>
                                <td>
                                    @if ($creator->pending_count > 0)
                                        <span class="text-amber-700 font-medium">{{ $creator->pending_count }}</span>
                                    @else
                                        <span class="text-stone-400">0</span>
                                    @endif
                                </td>
                                <td class="text-stone-500 tabular-nums">{{ $creator->last_run_date?->toDateString() ?? '—' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('operator.creators.show', $creator) }}" class="ops-link">Open hub</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="No creators yet">
                                        Onboard your first creator to start the weekly batch.
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
