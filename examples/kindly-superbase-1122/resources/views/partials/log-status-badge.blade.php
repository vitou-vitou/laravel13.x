@php
    $badgeClasses = match ($status) {
        'completed' => 'bg-emerald-100 text-emerald-800',
        'processing' => 'bg-sky-100 text-sky-800',
        'failed' => 'bg-red-100 text-red-800',
        'pending' => 'bg-amber-100 text-amber-800',
        default => 'bg-zinc-100 text-zinc-800',
    };
@endphp
<span class="rounded-full px-2.5 py-0.5 text-xs font-medium uppercase tracking-wide {{ $badgeClasses }}">
    {{ $status }}
</span>
