<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="ops-page-title">{{ '@'.$creator->handle }}</h2>
                <p class="ops-page-subtitle">Creator hub · publish log and batch workflow</p>
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                <a href="{{ route('operator.creators.publish-log.create', $creator) }}" class="ops-link">Add publish row (step 4)</a>
                <a href="{{ route('operator.creators.publish-log.export', $creator) }}" class="ops-link-muted">Export CSV</a>
                <a href="{{ route('operator.creators.edit', $creator) }}" class="ops-link-muted">Onboarding</a>
            </div>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container ops-stack">
            <x-flash />

            <x-creator-hub-nav :creator="$creator" />

            <x-batch-loop-rail :current="4" />

            <div class="ops-meta-grid">
                <div>
                    <div class="ops-meta-label">Tier</div>
                    <div class="ops-meta-value">{{ $creator->tier->label() }}</div>
                </div>
                <div>
                    <div class="ops-meta-label">Music policy</div>
                    <div class="ops-meta-value">{{ $creator->music_policy->label() }}</div>
                </div>
                <div>
                    <div class="ops-meta-label">Last run (BUILD LIST)</div>
                    <div class="ops-meta-value tabular-nums">{{ $creator->last_run_date?->toDateString() ?? '—' }}</div>
                </div>
                <div>
                    <div class="ops-meta-label">Pending approval</div>
                    <div class="ops-meta-value text-amber-700">{{ $statusCounts['pending_approval'] ?? 0 }}</div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('operator.creators.show', $creator) }}"
                   @class(['ops-chip-active' => ! $statusFilter, 'ops-chip-inactive' => $statusFilter])>
                    All
                </a>
                @foreach ($statuses as $status)
                    <a href="{{ route('operator.creators.show', [$creator, 'status' => $status->value]) }}"
                       @class(['ops-chip-active' => $statusFilter === $status->value, 'ops-chip-inactive' => $statusFilter !== $status->value])>
                        {{ $status->label() }}
                        @if (($statusCounts[$status->value] ?? 0) > 0)
                            <span class="opacity-70">({{ $statusCounts[$status->value] }})</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <x-ops-panel title="Publish log">
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th class="w-14"></th>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td>
                                    <x-tiktok-thumb
                                        :url="$entry->tiktok_url"
                                        :thumbnail="$entry->tiktok_thumbnail_url"
                                        size="sm"
                                    />
                                </td>
                                <td class="tabular-nums text-stone-500">{{ $entry->logged_on->toDateString() }}</td>
                                <td class="max-w-xs truncate font-medium">{{ $entry->title_variant ?? '—' }}</td>
                                <td><x-publish-status :status="$entry->status" /></td>
                                <td class="text-right space-x-3">
                                    <a href="{{ route('operator.creators.publish-log.edit', [$creator, $entry]) }}" class="ops-link">Edit</a>
                                    @if ($entry->status->isPublishable())
                                        <a href="{{ route('operator.creators.publish-log.edit', [$creator, $entry]) }}#publish" class="ops-link text-emerald-700 hover:text-emerald-900">Publish</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="No rows in publish log">
                                        Add from weekly batch step 4 (packaging).
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
