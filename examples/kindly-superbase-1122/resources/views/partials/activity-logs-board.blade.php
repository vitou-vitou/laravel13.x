<div class="relative" data-log-search-wrap>
    <label for="log-search" class="sr-only">Search by transaction ID</label>
    <input
        type="search"
        id="log-search"
        data-log-search
        value="{{ $activityLogs['search'] ?? '' }}"
        placeholder="Search transaction ID…"
        autocomplete="off"
        class="w-full rounded-md border border-zinc-200 bg-white py-2 pl-3 pr-[4.5rem] text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 [&::-webkit-search-cancel-button]:appearance-none"
    >

    <div class="absolute inset-y-0 right-1.5 flex items-center gap-0.5">
        <button
            type="button"
            data-log-clear
            aria-label="Clear filters"
            class="rounded p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700"
        >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l8 8M14 6l-8 8" />
            </svg>
        </button>

        <div class="relative" data-log-status-filter>
            <button
                type="button"
                data-log-filter-toggle
                aria-haspopup="true"
                aria-expanded="false"
                aria-label="Filter by status"
                class="rounded p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 data-[active=true]:bg-zinc-900 data-[active=true]:text-white"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 4h14l-5.5 6.5V16l-3 1.5v-7L3 4z" />
                </svg>
            </button>

            <div
                data-log-filter-menu
                role="menu"
                aria-label="Filter by status"
                class="absolute right-0 z-20 mt-1 hidden w-44 overflow-hidden rounded-md border border-zinc-200 bg-white py-1 shadow-lg"
            >
                <button
                    type="button"
                    role="menuitemradio"
                    class="log-status-filter w-full px-3 py-1.5 text-left text-xs font-medium text-zinc-600 hover:bg-zinc-50"
                    data-log-status-filter-value=""
                    aria-pressed="true"
                >
                    All
                </button>
                @foreach (\App\Models\ActivityLog::STATUSES as $status)
                    <button
                        type="button"
                        role="menuitemradio"
                        class="log-status-filter w-full px-3 py-1.5 text-left text-xs font-medium capitalize text-zinc-600 hover:bg-zinc-50"
                        data-log-status-filter-value="{{ $status }}"
                        aria-pressed="false"
                    >
                        {{ $status }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div data-log-entries>
    @php $visibleGroups = collect($activityLogs['groups'] ?? [])->filter(fn ($g) => $g['entries']->isNotEmpty()); @endphp
    @if ($visibleGroups->isEmpty())
        <p class="rounded-lg border border-dashed border-zinc-300 px-4 py-6 text-center text-zinc-500" data-log-empty>
            No log entries yet.
        </p>
    @else
        <div class="space-y-5" data-log-groups>
            @foreach ($visibleGroups as $group)
                <section data-log-group="{{ $group['status'] }}">
                    <div class="mb-2 flex items-center gap-2">
                        @include('partials.log-status-badge', ['status' => $group['status']])
                        <span class="text-xs font-medium text-zinc-400">{{ $group['total'] }}</span>
                    </div>
                    <ul class="divide-y divide-zinc-200 rounded-lg border border-zinc-200" data-log-list data-log-group-list="{{ $group['status'] }}">
                        @foreach ($group['entries'] as $entry)
                            @include('partials.activity-log-entry', ['entry' => $entry, 'logBoard' => $logBoard])
                        @endforeach
                    </ul>
                    @if ($group['has_more'])
                        @include('partials.log-load-more', ['status' => $group['status']])
                    @endif
                </section>
            @endforeach
        </div>
    @endif
</div>
