<x-app-layout>
    <x-store-page :title="'Dispute #'.$dispute->id" max="max-w-3xl">
        <x-flash-status class="mt-6" />

        <div class="store-panel mt-6 space-y-2 text-sm">
            <p>Status: <span class="font-semibold text-stone-900">{{ $dispute->status->value }}</span></p>
            <p class="text-stone-600">Vendor: {{ $dispute->orderGroup->vendor->store_name }}</p>
            <p class="text-stone-600">Order: #{{ $dispute->orderGroup->order_id }}</p>
        </div>

        <div class="store-panel mt-6 space-y-4">
            @foreach ($dispute->messages as $message)
                <div class="border-b border-stone-100 pb-3 last:border-0">
                    <p class="text-xs font-medium text-stone-500">{{ $message->user->name }}</p>
                    <p class="mt-1 text-sm text-stone-800">{{ $message->body }}</p>
                </div>
            @endforeach

            <form method="POST" action="{{ route('disputes.message', $dispute) }}" class="space-y-3 border-t border-stone-100 pt-4">
                @csrf
                <textarea name="body" rows="3" class="store-input" required placeholder="Your message…"></textarea>
                <button type="submit" class="btn-brand">Reply</button>
            </form>
        </div>
    </x-store-page>
</x-app-layout>
