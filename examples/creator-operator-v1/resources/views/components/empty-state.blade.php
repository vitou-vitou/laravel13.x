@props(['title' => null])

<div {{ $attributes->merge(['class' => 'ops-empty']) }}>
    @if ($title)
        <p class="ops-empty-title">{{ $title }}</p>
    @endif
    <p class="mt-1">{{ $slot }}</p>
</div>
