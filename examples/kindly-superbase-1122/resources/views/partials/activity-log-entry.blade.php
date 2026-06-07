@php
    $truncate = $logBoard->shouldTruncateMessage($entry->message);
    $txnDisplay = $entry->transaction_id ? \Illuminate\Support\Str::start($entry->transaction_id, 'sup_') : '';
    $entryJson = json_encode([
        'id' => $entry->id,
        'transaction_id' => $txnDisplay,
        'status' => $entry->status,
        'message' => $entry->message,
        'action' => $entry->action,
        'trigger' => $entry->context['trigger'] ?? null,
        'created_at' => $entry->created_at?->toIso8601String(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<li class="relative flex items-start justify-between gap-3 px-4 py-3" data-log-id="{{ $entry->id }}">
    <div class="min-w-0 flex-1 space-y-1">
        <p
            class="font-medium text-zinc-900"
            data-log-message
            @if ($truncate) data-log-message-truncated="true" data-log-message-full-b64="{{ base64_encode($entry->message) }}" @endif
        >
            <span data-log-message-text>{{ $truncate ? $logBoard->previewMessage($entry->message) : $entry->message }}</span>
            @if ($truncate)
                <button
                    type="button"
                    class="ml-1 inline text-xs font-medium text-sky-600 hover:text-sky-700"
                    data-log-message-toggle
                    aria-expanded="false"
                >
                    Show more
                </button>
            @endif
        </p>
        <p class="text-xs text-zinc-500">
            @if ($entry->transaction_id)
                txn {{ \Illuminate\Support\Str::start($entry->transaction_id, 'sup_') }}
                ·
            @endif
            @if ($entry->action)
                supabase · {{ str_replace('_', ' ', $entry->action) }}
                @if (! empty($entry->context['trigger']))
                    · {{ str_replace('_', ' ', $entry->context['trigger']) }}
                @endif
                ·
            @endif
            {{ $entry->created_at->diffForHumans() }}
        </p>
    </div>

    <div class="relative shrink-0" data-log-actions>
        <button
            type="button"
            data-log-actions-toggle
            aria-haspopup="true"
            aria-expanded="false"
            aria-label="Manage entry"
            class="rounded p-1 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700"
        >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <circle cx="10" cy="4" r="1.5" />
                <circle cx="10" cy="10" r="1.5" />
                <circle cx="10" cy="16" r="1.5" />
            </svg>
        </button>

        <div
            data-log-actions-menu
            role="menu"
            class="absolute right-0 z-20 mt-1 hidden w-44 overflow-hidden rounded-md border border-zinc-200 bg-white py-1 shadow-lg"
        >
            <button
                type="button"
                role="menuitem"
                data-log-copy="txn"
                data-log-copy-value="{{ $txnDisplay }}"
                class="block w-full px-3 py-1.5 text-left text-xs font-medium text-zinc-600 hover:bg-zinc-50"
            >
                Copy transaction ID
            </button>
            <button
                type="button"
                role="menuitem"
                data-log-copy="entry"
                data-log-copy-encoded="true"
                data-log-copy-value="{{ base64_encode($entryJson) }}"
                class="block w-full px-3 py-1.5 text-left text-xs font-medium text-zinc-600 hover:bg-zinc-50"
            >
                Copy log entry
            </button>
        </div>
    </div>
</li>
