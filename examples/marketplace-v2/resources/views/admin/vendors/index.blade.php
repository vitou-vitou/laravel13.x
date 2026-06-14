<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin — Vendors</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            @foreach ($vendors as $vendor)
                <div class="bg-white shadow rounded p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium">{{ $vendor->store_name }}</p>
                        <p class="text-sm text-gray-500">{{ $vendor->user->email }} — {{ $vendor->status->value }}</p>
                    </div>
                    @if ($vendor->status->value === 'pending')
                        <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">
                            @csrf
                            <button class="px-3 py-1 bg-gray-800 text-white rounded text-sm">Approve</button>
                        </form>
                    @elseif ($vendor->status->value === 'active')
                        <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}">
                            @csrf
                            <button class="px-3 py-1 border rounded text-sm">Suspend</button>
                        </form>
                    @elseif ($vendor->status->value === 'suspended')
                        <form method="POST" action="{{ route('admin.vendors.activate', $vendor) }}">
                            @csrf
                            <button class="px-3 py-1 bg-gray-800 text-white rounded text-sm">Reactivate</button>
                        </form>
                    @endif
                </div>
            @endforeach

            {{ $vendors->links() }}
        </div>
    </div>
</x-app-layout>
