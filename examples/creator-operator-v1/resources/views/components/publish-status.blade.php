@props(['status'])

@php
    $classes = match ($status->value) {
        'pending_approval' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-sky-100 text-sky-800',
        'published' => 'bg-emerald-100 text-emerald-800',
        'skipped_music', 'skipped_creator' => 'bg-stone-100 text-stone-700',
        'error' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $status->label() }}
</span>
