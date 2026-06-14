@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-stone-900 bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition'
            : 'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-stone-500 hover:text-stone-800 hover:bg-stone-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
