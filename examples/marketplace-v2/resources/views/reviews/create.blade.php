<x-app-layout>
    <x-store-page title="Write a review" max="max-w-lg">
        <form method="POST" action="{{ route('reviews.store', [$order, $product]) }}" class="store-panel mt-6 space-y-4">
            @csrf
            <p class="text-sm text-stone-500">{{ $product->name }} — order #{{ $order->id }}</p>
            <div>
                <label class="block text-sm font-medium text-stone-700">Rating (1–5)</label>
                <select name="rating" class="store-input mt-1" required>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700">Review</label>
                <textarea name="body" rows="4" class="store-input mt-1" required></textarea>
            </div>
            <button type="submit" class="btn-brand">Submit review</button>
        </form>
    </x-store-page>
</x-app-layout>
