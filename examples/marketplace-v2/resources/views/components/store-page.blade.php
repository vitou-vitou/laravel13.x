@props(['title' => null, 'max' => 'max-w-5xl'])

<div class="bg-stone-50 py-10">
    <div {{ $attributes->merge(['class' => "mx-auto px-4 sm:px-6 lg:px-8 {$max}"]) }}>
        @if ($title)
            <h1 class="text-2xl font-bold tracking-tight text-stone-900">{{ $title }}</h1>
        @endif
        {{ $slot }}
    </div>
</div>
