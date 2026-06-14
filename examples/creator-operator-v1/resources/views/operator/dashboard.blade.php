<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Batch queue</h2>
            <p class="ops-page-subtitle">Cross-creator publish log health at a glance</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container ops-stack">
            <x-flash />

            <x-batch-loop-rail />

            <div class="ops-kpi-grid">
                <div class="ops-kpi">
                    <div class="ops-kpi-label">Creators</div>
                    <div class="ops-kpi-value">{{ $creatorsCount }}</div>
                </div>
                <div class="ops-kpi ops-kpi--amber">
                    <div class="ops-kpi-label">Step 5 · Pending approval</div>
                    <div class="ops-kpi-value">{{ $pendingCount }}</div>
                </div>
                <div class="ops-kpi ops-kpi--sky">
                    <div class="ops-kpi-label">Step 6 · Ready to publish</div>
                    <div class="ops-kpi-value">{{ $approvedCount }}</div>
                </div>
                <div class="ops-kpi ops-kpi--emerald">
                    <div class="ops-kpi-label">Published (7d)</div>
                    <div class="ops-kpi-value">{{ $publishedThisWeek }}</div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('operator.creators.index') }}" class="ops-btn-primary">All creators</a>
                <a href="{{ route('operator.creators.create') }}" class="ops-btn-secondary">Onboard creator</a>
            </div>

            <x-ops-panel title="Publish log · recent">
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th>Creator</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Logged</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEntries as $entry)
                            <tr>
                                <td class="font-medium">{{ $entry->creator->handle }}</td>
                                <td class="truncate max-w-xs">{{ $entry->title_variant ?? '—' }}</td>
                                <td><x-publish-status :status="$entry->status" /></td>
                                <td class="text-stone-500 tabular-nums">{{ $entry->logged_on->toDateString() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="No publish log rows yet">
                                        Start at step 4 (packaging) on a creator hub.
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
