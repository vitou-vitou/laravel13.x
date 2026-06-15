@props([
    'url' => null,
    'thumbnail' => null,
    'title' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'ops-thumb--sm',
        'md' => 'ops-thumb--md',
        'lg' => 'ops-thumb--lg',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'ops-thumb '.$sizeClass]) }}>
    @if ($thumbnail)
        <a href="{{ $url }}" target="_blank" rel="noopener" class="ops-thumb-link" aria-label="{{ $title ? 'Open TikTok: '.$title : 'Open TikTok source' }}">
            <img
                src="{{ $thumbnail }}"
                alt=""
                class="ops-thumb-img"
                loading="lazy"
                decoding="async"
            />
        </a>
    @elseif ($url)
        <a href="{{ $url }}" target="_blank" rel="noopener" class="ops-thumb-placeholder ops-thumb-link" aria-label="Open TikTok source">
            <span class="ops-thumb-placeholder-icon" aria-hidden="true">▶</span>
            <span class="ops-thumb-placeholder-label">Open TikTok</span>
        </a>
    @else
        <div class="ops-thumb-placeholder" aria-hidden="true">
            <span class="ops-thumb-placeholder-icon">▶</span>
        </div>
    @endif
</div>
