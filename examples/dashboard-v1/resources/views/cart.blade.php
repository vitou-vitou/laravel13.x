<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Shopping Cart') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($cart->items->isEmpty())
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Your cart is empty.') }}</p>
                    @else
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($cart->items as $item)
                                <li class="py-4 flex justify-between gap-4">
                                    <div>
                                        <p class="font-medium">{{ $item->product->getTranslation('name', 'en') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('Qty: :qty', ['qty' => $item->quantity]) }}</p>
                                    </div>
                                    <p class="font-medium">{{ $item->product->formattedPrice() }}</p>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-lg font-semibold">
                                {{ __('Total: :total', ['total' => '$'.number_format($totalCents / 100, 2)]) }}
                            </p>
                            <form method="POST" action="{{ route('cart.checkout') }}">
                                @csrf
                                <x-primary-button>
                                    {{ __('Place order') }}
                                </x-primary-button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
