<x-app-layout>
    <form method="POST" action="{{ route('customers.update', $customer) }}" class="p-6 space-y-4">
        @csrf @method('PUT')
        <input name="name" class="border p-2" value="{{ old('name', $customer->name) }}">
        <input name="email" class="border p-2" value="{{ old('email', $customer->email) }}">
        <textarea name="address" class="border p-2">{{ old('address', $customer->address) }}</textarea>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2">Update</button>
    </form>
</x-app-layout>
