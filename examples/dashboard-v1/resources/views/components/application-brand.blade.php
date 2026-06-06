@props([
    'href' => null,
    'compact' => false,
])

@php
    $name = config('app.name', 'Dashboard');
    $iconWrapperClass = $compact ? 'h-8 w-8 rounded-md' : 'h-10 w-10 rounded-lg';
    $textClass = $compact ? 'text-base font-semibold' : 'text-lg font-semibold';
    $iconClass = $compact ? 'h-4 w-4' : 'h-5 w-5';
    $tagClass = 'inline-flex items-center gap-2.5';
    $focusClass = $href ? ' rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2' : '';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $tagClass.$focusClass]) }}>
@else
    <div {{ $attributes->merge(['class' => $tagClass]) }}>
@endif
        <span @class([
            'inline-flex shrink-0 items-center justify-center bg-amber-500/10 text-amber-600',
            $iconWrapperClass,
        ])>
            <x-icons.chart-dashboard class="{{ $iconClass }}" />
        </span>
        <span @class([$textClass, 'text-gray-900'])>{{ $name }}</span>
@if ($href)
    </a>
@else
    </div>
@endif
