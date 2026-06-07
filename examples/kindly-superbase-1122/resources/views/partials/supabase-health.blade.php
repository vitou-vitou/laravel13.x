@php
    $isIdle = $supabaseHealth === null;
    $badgeClasses = match ($supabaseHealth['status'] ?? 'idle') {
        'healthy' => 'bg-emerald-100 text-emerald-800',
        'unhealthy' => 'bg-red-100 text-red-800',
        'idle' => 'bg-zinc-100 text-zinc-600',
        default => 'bg-amber-100 text-amber-800',
    };
    $statusLabel = $isIdle ? 'idle' : str_replace('_', ' ', $supabaseHealth['status']);
@endphp

<div class="relative space-y-4" data-supabase-health>
    <div
        data-supabase-loading
        class="absolute inset-0 z-10 hidden items-center justify-center rounded-lg bg-white/80 backdrop-blur-sm"
        aria-hidden="true"
    >
        <p class="flex items-center gap-2 text-sm font-medium text-zinc-700">
            <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-900"></span>
            Checking Supabase…
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <h3 class="text-base font-semibold text-zinc-900">Health</h3>
        <span
            data-supabase-status="{{ $isIdle ? 'idle' : $supabaseHealth['status'] }}"
            class="rounded-full px-2.5 py-0.5 text-xs font-medium uppercase tracking-wide {{ $badgeClasses }}"
        >
            {{ $statusLabel }}
        </span>
        <button
            type="button"
            data-supabase-health-check
            data-health-check-url="{{ route('supabase.health-check') }}"
            class="ml-auto rounded-md bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-60"
        >
            Test connection
        </button>
    </div>

    <p class="hidden text-xs text-red-600" data-health-error role="alert"></p>

    <dl class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-xs space-y-2">
        <div class="grid grid-cols-[6rem_1fr] gap-2">
            <dt class="font-medium text-zinc-500">Endpoint</dt>
            <dd class="break-all text-zinc-800" data-health-endpoint>
                {{ $isIdle ? 'Waiting for check…' : ($supabaseHealth['endpoint'] ?? 'Not configured') }}
            </dd>
        </div>
        <div class="grid grid-cols-[6rem_1fr] gap-2">
            <dt class="font-medium text-zinc-500">Checked</dt>
            <dd class="text-zinc-800" data-health-checked-at>{{ $supabaseHealth['checked_at'] ?? '—' }}</dd>
        </div>
        <div
            class="grid grid-cols-[6rem_1fr] gap-2 @if ($isIdle || empty($supabaseHealth['transaction_id'] ?? null)) hidden @endif"
            data-health-transaction-row
        >
            <dt class="font-medium text-zinc-500">Txn ID</dt>
            <dd class="break-all font-mono text-zinc-800" data-health-transaction-id>{{ $supabaseHealth['transaction_id'] ?? '' }}</dd>
        </div>
        <div
            class="grid grid-cols-[6rem_1fr] gap-2 @if ($isIdle || ! isset($supabaseHealth['http_status'])) hidden @endif"
            data-health-http-row
        >
            <dt class="font-medium text-zinc-500">HTTP</dt>
            <dd class="text-zinc-800" data-health-http-status>{{ $supabaseHealth['http_status'] ?? '' }}</dd>
        </div>
        <div class="grid grid-cols-[6rem_1fr] gap-2">
            <dt class="font-medium text-zinc-500">Message</dt>
            <dd class="text-zinc-800" data-health-message>
                {{ $isIdle ? 'Open the supabase tab or click Test connection to run a health check.' : $supabaseHealth['message'] }}
            </dd>
        </div>
    </dl>

    <div
        data-health-details
        @class(['hidden' => $isIdle || empty($supabaseHealth['details'] ?? [])])
    >
        <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-500">Auth service</h4>
        <dl class="rounded-lg border border-zinc-200 bg-white p-4 text-xs" data-health-details-list>
            @foreach ($supabaseHealth['details'] ?? [] as $key => $value)
                <div class="grid grid-cols-[8rem_1fr] gap-2 py-1">
                    <dt class="font-medium text-zinc-500">{{ $key }}</dt>
                    <dd class="text-zinc-800">{{ is_scalar($value) ? $value : json_encode($value) }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</div>
