@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-2">Kindly E-Commerce</h1>
    <p class="text-gray-600 mb-8">Browse our catalog and add items to your cart.</p>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            <article class="bg-white rounded-lg shadow p-6 flex flex-col">
                <h2 class="text-lg font-semibold">{{ $product->name }}</h2>
                <p class="text-sm text-gray-600 mt-2 flex-1">{{ $product->description }}</p>
                <p class="mt-4 font-medium">{{ $product->formattedPrice() }}</p>
                <p class="text-xs text-gray-500">In stock: {{ $product->stock_quantity }}</p>
                <form method="POST" action="{{ route('cart.store') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="w-full bg-gray-800 text-white rounded-md px-4 py-2 text-sm hover:bg-gray-700">
                        Add to cart
                    </button>
                </form>
            </article>
        @empty
            <p class="text-gray-600">No products available.</p>
        @endforelse
    </div>
@endsection
