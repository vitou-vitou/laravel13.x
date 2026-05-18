<x-app-layout>
    <div class="p-6">
        <h1>{{ $customer->name }}</h1>
        <p>{{ $customer->email }}</p>
        <p>{{ $customer->address }}</p>
    </div>
</x-app-layout>
