<x-app-layout>
    <x-store-page title="Wishlist" max="max-w-7xl">
        <x-flash-status class="mt-6" />

        @if ($products->isEmpty())
            <div class="store-card mt-6 p-10 text-center">
                <p class="text-lg font-medium text-stone-900">Your wishlist is empty</p>
                <a href="{{ route('catalog.index') }}" class="btn-brand mt-6 inline-flex">Browse products</a>
            </div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <div class="store-card overflow-hidden">
                        <a href="{{ route('catalog.show', $product) }}">
                            <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                        </a>
                        <div class="p-4 space-y-3">
                            <a href="{{ route('catalog.show', $product) }}" class="font-semibold text-stone-900 hover:text-brand-700">{{ $product->name }}</a>
                            <p class="text-sm text-stone-500">{{ $product->vendor->store_name }}</p>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('wishlist.cart', $product) }}">
                                    @csrf
                                    <button type="submit" class="btn-brand text-sm">Add to cart</button>
                                </form>
                                <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-brand-outline text-sm">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-store-page>
</x-app-layout>
