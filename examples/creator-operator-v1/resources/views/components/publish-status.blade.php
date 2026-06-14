@props(['status'])

@php
    $classes = match ($status->value) {
        'pending_approval' => 'bg-amber-100 text-amber-900 ring-amber-200/80',
        'approved' => 'bg-sky-100 text-sky-900 ring-sky-200/80',
        'published' => 'bg-emerald-100 text-emerald-900 ring-emerald-200/80',
        'skipped_music', 'skipped_creator' => 'bg-stone-100 text-stone-700 ring-stone-200/80',
        'error' => 'bg-red-100 text-red-900 ring-red-200/80',
        default => 'bg-stone-100 text-stone-700 ring-stone-200/80',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {$classes}"]) }}>
    {{ $status->label() }}
</span>
