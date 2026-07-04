<x-app-layout>
    <x-store-page title="Shipping addresses" max="max-w-3xl">
        <x-flash-status class="mt-6" />

        <div class="store-panel mt-6 space-y-4">
            <h2 class="font-semibold text-stone-900">Add address</h2>
            @include('account.addresses._form', [
                'action' => route('account.addresses.store'),
                'method' => 'POST',
            ])
        </div>

        <div class="mt-8 space-y-4">
            @forelse ($addresses as $address)
                <div class="store-panel space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-stone-900">
                                {{ $address->label }}
                                @if ($address->is_default)
                                    <span class="ms-2 rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-800">Default</span>
                                @endif
                            </p>
                            <p class="mt-1 text-sm text-stone-600">{{ $address->name }}</p>
                            <p class="text-sm text-stone-600">{{ $address->formattedSingleLine() }}</p>
                        </div>
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-700">Remove</button>
                        </form>
                    </div>
                    @include('account.addresses._form', [
                        'action' => route('account.addresses.update', $address),
                        'method' => 'PATCH',
                        'address' => $address,
                    ])
                </div>
            @empty
                <div class="store-panel text-center text-stone-500">
                    No saved addresses yet.
                </div>
            @endforelse
        </div>
    </x-store-page>
</x-app-layout>
