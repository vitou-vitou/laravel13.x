<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $customer->name }}</h2>
            <a href="{{ route('customers.edit', $customer) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg">
                <dl class="divide-y divide-gray-100">
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $customer->name }}</dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><a href="mailto:{{ $customer->email }}" class="text-indigo-600 hover:text-indigo-500">{{ $customer->email }}</a></dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="text-sm text-gray-900 col-span-2 whitespace-pre-line">{{ $customer->address }}</dd>
                    </div>
                </dl>
            </div>
            <div class="mt-4">
                <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to customers</a>
            </div>
        </div>
    </div>
</x-app-layout>
