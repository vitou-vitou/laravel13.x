<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shop</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <form method="GET" action="{{ route('catalog.index') }}" class="flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products" class="border rounded px-3 py-2 flex-1">
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Search</button>
            </form>

            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('catalog.index') }}" class="px-3 py-1 rounded border {{ !request('category') ? 'bg-gray-800 text-white' : '' }}">All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                       class="px-3 py-1 rounded border {{ request('category') === $category->slug ? 'bg-gray-800 text-white' : '' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                @foreach ($products as $product)
                    <div class="bg-white shadow rounded p-4">
                        <h3 class="font-semibold">
                            <a href="{{ route('catalog.show', $product) }}" class="hover:underline">{{ $product->name }}</a>
                        </h3>
                        <p class="text-sm text-gray-500">{{ $product->vendor->store_name }}</p>
                        @if ($product->variants->first())
                            <p class="mt-2 font-medium">{{ $product->variants->first()->formattedPrice() }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
