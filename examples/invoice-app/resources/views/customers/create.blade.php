<x-app-layout>
    <form method="POST" action="{{ route('customers.store') }}" class="p-6 space-y-4">
        @csrf
        <input name="name" placeholder="Name" class="border p-2" value="{{ old('name') }}">
        <input name="email" placeholder="Email" class="border p-2" value="{{ old('email') }}">
        <textarea name="address" placeholder="Address" class="border p-2">{{ old('address') }}</textarea>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Create</button>
    </form>
</x-app-layout>
