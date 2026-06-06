<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center gap-4">
                        @if (Auth::user()->avatar)
                            <img
                                src="{{ Auth::user()->avatar }}"
                                alt=""
                                class="h-12 w-12 rounded-full border border-gray-200"
                            />
                        @endif
                        <div>
                            <p class="font-medium">{{ Auth::user()->name }}</p>
                            <p class="text-sm text-gray-600">{{ __("You're logged in!") }}</p>
                            @if (Auth::user()->github_id)
                                <p class="text-xs text-gray-500">{{ __('Signed in via GitHub') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
