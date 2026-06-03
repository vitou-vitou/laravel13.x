@props(['active' => false])
<svg class="size-7 sm:size-8" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $active ? '0' : '2' }}" aria-hidden="true">
    <rect x="3" y="6" width="18" height="13" rx="2" @unless($active) fill="none" @endunless />
    <path d="M10 10.5v4l4-2-4-2z" fill="{{ $active ? '#242526' : 'currentColor' }}" />
</svg>
