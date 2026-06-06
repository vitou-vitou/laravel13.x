@props([
    'provider',
    'href',
])

@php
    $config = match ($provider) {
        'google' => [
            'label' => __('Sign in with Google'),
            'border' => 'border-[#747775]',
            'text' => 'text-[#1F1F1F]',
            'focus' => 'focus-visible:border-[#1f1f1f] focus-visible:ring-[#1f1f1f]/20',
            'hover' => 'hover:bg-[#f8f9fa]',
        ],
        'microsoft' => [
            'label' => __('Sign in with Microsoft'),
            'border' => 'border-[#8c8c8c]',
            'text' => 'text-[#5e5e5e]',
            'focus' => 'focus-visible:border-[#5e5e5e] focus-visible:ring-[#5e5e5e]/20',
            'hover' => 'hover:bg-[#f8f8f8]',
        ],
        default => [
            'label' => __('Sign in with :provider', ['provider' => ucfirst($provider)]),
            'border' => 'border-gray-300',
            'text' => 'text-gray-700',
            'focus' => 'focus-visible:border-gray-500 focus-visible:ring-gray-500/20',
            'hover' => 'hover:bg-gray-50',
        ],
    };

    $label = $config['label'];
@endphp

<a
    href="{{ $href }}"
    aria-label="{{ $label }}"
    {{ $attributes->merge([
        'class' => implode(' ', [
            'group flex min-h-[41px] w-full touch-manipulation items-center justify-center gap-3 rounded border bg-white px-3 py-2 shadow-none transition-[color,background-color,border-color,box-shadow] duration-150 ease-out',
            'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 active:bg-gray-100 motion-reduce:transition-none',
            $config['border'],
            $config['text'],
            $config['focus'],
            $config['hover'],
        ]),
    ]) }}
>
    @if ($provider === 'microsoft' || $provider === 'google')
        <span class="pointer-events-none flex shrink-0 items-center justify-center" aria-hidden="true">
            @if ($provider === 'microsoft')
                <x-microsoft-icon class="block h-5 w-5 sm:h-[18px] sm:w-[18px]" />
            @else
                <x-google-icon class="block h-5 w-5 sm:h-[18px] sm:w-[18px]" />
            @endif
        </span>
    @endif
    <span class="pointer-events-none text-sm font-medium leading-normal">
        {{ $label }}
    </span>
</a>
