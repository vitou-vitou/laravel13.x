<x-app-layout>
    <x-store-page title="Admin — Vendors">
        <x-admin-subnav class="mt-6" />
        <x-flash-status class="mt-6" />

        <div class="mt-6 space-y-3">
            @foreach ($vendors as $vendor)
                <div class="store-panel flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-stone-900">{{ $vendor->store_name }}</p>
                        <p class="text-sm text-stone-500">{{ $vendor->user->email }} — {{ $vendor->status->value }}</p>
                    </div>
                    @if ($vendor->status->value === 'pending')
                        <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">
                            @csrf
                            <button type="submit" class="btn-brand text-sm">Approve</button>
                        </form>
                    @elseif ($vendor->status->value === 'active')
                        <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}">
                            @csrf
                            <button type="submit" class="btn-brand-outline text-sm">Suspend</button>
                        </form>
                    @elseif ($vendor->status->value === 'suspended')
                        <form method="POST" action="{{ route('admin.vendors.activate', $vendor) }}">
                            @csrf
                            <button type="submit" class="btn-brand text-sm">Reactivate</button>
                        </form>
                    @endif
                </div>
            @endforeach
            {{ $vendors->links() }}
        </div>
    </x-store-page>
</x-app-layout>
