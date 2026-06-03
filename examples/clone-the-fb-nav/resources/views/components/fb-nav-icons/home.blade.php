@props(['active' => false])
<svg class="size-7 sm:size-8" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $active ? '0' : '2' }}" aria-hidden="true">
    <path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z" @unless($active) stroke-linejoin="round" @endunless />
</svg>
