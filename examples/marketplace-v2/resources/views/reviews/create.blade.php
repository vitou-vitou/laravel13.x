<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Write a review</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('reviews.store', [$order, $product]) }}" class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600">{{ $product->name }} — order #{{ $order->id }}</p>
                <div>
                    <label class="block text-sm font-medium">Rating (1–5)</label>
                    <select name="rating" class="border rounded w-full mt-1" required>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Review</label>
                    <textarea name="body" rows="4" class="border rounded w-full mt-1" required></textarea>
                </div>
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Submit review</button>
            </form>
        </div>
    </div>
</x-app-layout>
