@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 rounded-lg text-start text-base font-medium text-stone-900 bg-stone-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition'
            : 'block w-full ps-3 pe-4 py-2 rounded-lg text-start text-base font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
