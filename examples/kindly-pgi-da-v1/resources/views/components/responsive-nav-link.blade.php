@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg border-l-4 border-brand-600 bg-brand-50 py-2 ps-3 pe-4 text-start text-base font-semibold text-brand-700'
            : 'block w-full rounded-lg border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-stone-600 hover:border-stone-300 hover:bg-stone-50 hover:text-stone-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
