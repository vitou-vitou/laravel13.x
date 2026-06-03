@props(['active' => false])
<svg class="size-7 sm:size-8" viewBox="0 0 24 24" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
    <path d="M8 12h3M9.5 10.5v3" stroke-linecap="round" />
    <circle cx="15.5" cy="11" r="1" fill="currentColor" stroke="none" />
    <circle cx="17.5" cy="13" r="1" fill="currentColor" stroke="none" />
    <path d="M6 8h12a3 3 0 0 1 3 3v2a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-2a3 3 0 0 1 3-3z" />
</svg>
