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

            <div class="ops-chart-grid">
                <x-ops-bar-chart
                    title="Publish velocity · 7 days"
                    :series="$publishVelocity"
                    accent="emerald"
                    empty="No publishes in the last week."
                />
                <x-ops-bar-chart
                    title="Pending queue · logged this week"
                    :series="$pendingTrend"
                    accent="amber"
                    empty="No new pending rows this week."
                />
            </div>

            @if (count($pendingByCreator) > 0)
                <x-ops-panel title="Pending by creator">
                    <div class="divide-y divide-stone-100">
                        @foreach ($pendingByCreator as $row)
                            @php
                                $maxPending = max(1, collect($pendingByCreator)->max('value'));
                                $width = max(8, (int) round(($row['value'] / $maxPending) * 100));
                            @endphp
                            <div class="px-5 py-3 flex items-center gap-4 text-sm">
                                <span class="w-28 shrink-0 font-medium text-stone-800 truncate">{{ $row['label'] }}</span>
                                <div class="flex-1 h-2 rounded-full bg-stone-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $width }}%"></div>
                                </div>
                                <span class="w-8 text-right tabular-nums text-stone-600">{{ $row['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-ops-panel>
            @endif

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('operator.creators.index') }}" class="ops-btn-primary">All creators</a>
                <a href="{{ route('operator.creators.create') }}" class="ops-btn-secondary">Onboard creator</a>
            </div>

            <x-ops-panel title="Publish log · recent">
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th class="w-14"></th>
                            <th>Creator</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Logged</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEntries as $entry)
                            <tr>
                                <td>
                                    <x-tiktok-thumb
                                        :url="$entry->tiktok_url"
                                        :thumbnail="$entry->tiktok_thumbnail_url"
                                        size="sm"
                                    />
                                </td>
                                <td class="font-medium">{{ $entry->creator->handle }}</td>
                                <td class="truncate max-w-xs">{{ $entry->title_variant ?? '—' }}</td>
                                <td><x-publish-status :status="$entry->status" /></td>
                                <td class="text-stone-500 tabular-nums">{{ $entry->logged_on->toDateString() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
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
