<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Shop') }}
            </h2>
            <a href="{{ route('cart') }}"
               class="inline-flex items-center justify-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700">
                {{ __('View cart') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('cart_added'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ __('Added :product to your cart.', ['product' => session('cart_added')]) }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('shop.index') }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm font-medium',
                       'bg-gray-800 text-white' => $selectedCategory === null,
                       'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' => $selectedCategory !== null,
                   ])>
                    {{ __('All') }}
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                       @class([
                           'rounded-full px-4 py-2 text-sm font-medium',
                           'bg-gray-800 text-white' => $selectedCategory === $category->slug,
                           'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' => $selectedCategory !== $category->slug,
                       ])>
                        {{ $category->getTranslation('name', 'en') }}
                        <span class="text-xs opacity-75">({{ $category->products_count }})</span>
                    </a>
                @endforeach
            </div>

            @if ($products->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        {{ __('No products in this category yet.') }}
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                            <div class="p-6 flex-1">
                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $product->category?->getTranslation('name', 'en') }}
                                </p>
                                <h3 class="mt-2 text-lg font-semibold text-gray-900">
                                    {{ $product->getTranslation('name', 'en') }}
                                </h3>
                                <p class="mt-2 text-sm text-gray-600 line-clamp-3">
                                    {{ $product->getTranslation('description', 'en') }}
                                </p>
                                <p class="mt-4 text-lg font-semibold text-gray-900">
                                    {{ $product->formattedPrice() }}
                                </p>
                            </div>
                            <div class="border-t border-gray-100 px-6 py-4">
                                <form method="POST" action="{{ route('shop.cart.add', $product) }}" class="flex items-center gap-3">
                                    @csrf
                                    @if ($selectedCategory)
                                        <input type="hidden" name="category" value="{{ $selectedCategory }}">
                                    @endif
                                    <label class="sr-only" for="quantity-{{ $product->id }}">{{ __('Quantity') }}</label>
                                    <input id="quantity-{{ $product->id }}"
                                           name="quantity"
                                           type="number"
                                           min="1"
                                           value="1"
                                           class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <x-primary-button>
                                        {{ __('Add to cart') }}
                                    </x-primary-button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
