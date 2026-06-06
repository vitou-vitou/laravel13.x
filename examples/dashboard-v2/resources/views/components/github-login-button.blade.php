@props([
    'href',
])

<a
    href="{{ $href }}"
    aria-label="{{ __('Sign in with GitHub') }}"
    x-data="{ navigating: false }"
    x-on:click="if (navigating) { $event.preventDefault(); return; } navigating = true"
    x-bind:class="{ 'pointer-events-none opacity-50 cursor-wait': navigating }"
    {{ $attributes->merge([
        'class' => 'group flex min-h-[41px] w-full touch-manipulation items-center justify-center gap-3 rounded border border-[#24292f] bg-[#24292f] px-3 py-2 text-white shadow-none transition-[color,background-color,border-color,box-shadow,transform,opacity] duration-150 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-[#24292f]/30 focus-visible:ring-offset-2 active:scale-[0.97] motion-reduce:transition-none [@media(hover:hover)_and_(pointer:fine)]:hover:bg-[#1b1f23]',
    ]) }}
>
    <span class="pointer-events-none flex shrink-0 items-center justify-center" aria-hidden="true">
        <svg class="block h-5 w-5" viewBox="0 0 98 96" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.436-5.787-16.436-5.787-2.22-5.623-5.418-7.112-5.418-7.112-4.429-3.027.34-2.967.34-2.967 4.908.345 7.491 5.033 7.491 5.033 4.361 7.468 11.438 5.311 14.224 4.061.444-3.158 1.703-5.311 3.099-6.535-10.832-1.22-22.221-5.412-22.221-24.158 0-5.335 1.908-9.694 5.034-13.117-.504-1.233-2.185-6.185.478-12.892 0 0 4.106-1.311 13.443 5.008a46.647 46.647 0 0 1 12.214-1.642c4.142.046 8.319.558 12.214 1.642 9.337-6.319 13.443-5.008 13.443-5.008 2.663 6.707.982 11.659.478 12.892 3.126 3.423 5.034 7.782 5.034 13.117 0 18.791-11.416 22.934-22.271 24.158 1.752 1.512 3.308 4.471 3.308 9.035 0 6.525-.059 11.775-.059 13.377 0 1.304.881 2.857 3.316 2.364C84.007 89.386 98 70.871 98 49.217 98 22 76.245 0 48.854 0z"/>
        </svg>
    </span>
    <span class="pointer-events-none text-sm font-medium leading-normal">
        {{ __('Sign in with GitHub') }}
    </span>
</a>
