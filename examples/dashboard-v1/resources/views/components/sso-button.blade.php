@props([
    'provider',
    'href',
])

@php
    $config = match ($provider) {
        'google' => [
            'label' => __('Sign in with Google'),
            'border' => 'border-[#747775] dark:border-[#8e918f]',
            'text' => 'text-[#1F1F1F] dark:text-[#e3e3e3]',
            'focus' => 'focus-visible:border-[#1f1f1f] focus-visible:ring-[#1f1f1f]/20 dark:focus-visible:border-[#c4c7c5] dark:focus-visible:ring-[#c4c7c5]/20',
            'hover' => '[@media(hover:hover)_and_(pointer:fine)]:hover:bg-[#f8f9fa] [@media(hover:hover)_and_(pointer:fine)]:dark:hover:bg-[#292a2d]',
        ],
        'microsoft' => [
            'label' => __('Sign in with Microsoft'),
            'border' => 'border-[#8c8c8c] dark:border-[#6e6e6e]',
            'text' => 'text-[#5e5e5e] dark:text-[#e3e3e3]',
            'focus' => 'focus-visible:border-[#5e5e5e] focus-visible:ring-[#5e5e5e]/20 dark:focus-visible:border-[#adadad] dark:focus-visible:ring-[#adadad]/20',
            'hover' => '[@media(hover:hover)_and_(pointer:fine)]:hover:bg-[#f8f8f8] [@media(hover:hover)_and_(pointer:fine)]:dark:hover:bg-gray-700',
        ],
        'github' => [
            'label' => __('Sign in with GitHub'),
            'border' => 'border-[#24292f]',
            'text' => 'text-white',
            'focus' => 'focus-visible:border-[#24292f] focus-visible:ring-[#24292f]/30',
            'hover' => '[@media(hover:hover)_and_(pointer:fine)]:hover:bg-[#1b1f23]',
            'background' => 'bg-[#24292f]',
        ],
        default => [
            'label' => __('Sign in with :provider', ['provider' => ucfirst($provider)]),
            'border' => 'border-gray-300 dark:border-gray-600',
            'text' => 'text-gray-700 dark:text-gray-200',
            'focus' => 'focus-visible:border-gray-500 focus-visible:ring-gray-500/20 dark:focus-visible:border-gray-400 dark:focus-visible:ring-gray-400/20',
            'hover' => '[@media(hover:hover)_and_(pointer:fine)]:hover:bg-gray-50 [@media(hover:hover)_and_(pointer:fine)]:dark:hover:bg-gray-700',
        ],
    };

    $label = $config['label'];
@endphp

<a
    href="{{ $href }}"
    aria-label="{{ $label }}"
    x-data="{ navigating: false }"
    x-on:click="if (navigating) { $event.preventDefault(); return; } navigating = true"
    x-bind:class="{ 'pointer-events-none opacity-50 cursor-wait': navigating }"
    {{ $attributes->merge([
        'class' => implode(' ', [
            'group flex min-h-[41px] w-full touch-manipulation items-center justify-center gap-3 rounded border px-3 py-2 shadow-none transition-[color,background-color,border-color,box-shadow,transform,opacity] duration-150 ease-out',
            $config['background'] ?? 'bg-white dark:bg-gray-800',
            'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800 active:bg-gray-100 dark:active:bg-gray-700 active:scale-[0.97] motion-reduce:transition-none',
            $config['border'],
            $config['text'],
            $config['focus'],
            $config['hover'],
        ]),
    ]) }}
>
    @if (in_array($provider, ['microsoft', 'google', 'github'], true))
        <span class="pointer-events-none flex shrink-0 items-center justify-center" aria-hidden="true">
            @if ($provider === 'microsoft')
                <x-microsoft-icon class="block h-5 w-5 sm:h-[18px] sm:w-[18px]" />
            @elseif ($provider === 'github')
                <x-github-icon class="block h-5 w-5 sm:h-[18px] sm:w-[18px]" />
            @else
                <x-google-icon class="block h-5 w-5 sm:h-[18px] sm:w-[18px]" />
            @endif
        </span>
    @endif
    <span class="pointer-events-none text-sm font-medium leading-normal">
        {{ $label }}
    </span>
</a>
