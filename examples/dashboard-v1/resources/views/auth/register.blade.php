<x-guest-layout>
    <form
        method="POST"
        action="{{ route('register') }}"
        x-data="{ submitting: false }"
        x-on:submit="submitting = true"
    >
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-primary-button
                class="w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-150 ease-out"
                x-bind:disabled="submitting"
                x-bind:class="{ 'pointer-events-none': submitting }"
            >
                {{ __('Register') }}
            </x-primary-button>

            @if (Route::has('login'))
                <p class="mt-3 text-sm text-center text-gray-600">
                    {{ __('Already have an account?') }}
                    <a class="font-medium text-blue-600 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-sm no-underline hover:no-underline" href="{{ route('login') }}">{{ __('Login') }}</a>
                </p>
            @endif
        </div>
    </form>
</x-guest-layout>
