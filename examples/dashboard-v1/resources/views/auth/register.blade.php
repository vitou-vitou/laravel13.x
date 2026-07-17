<x-guest-layout>
    <header class="mb-6">
        <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
            {{ __('Sign up') }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Create an account with your email.') }}
        </p>
    </header>

    <x-auth-sso-options />

    <form
        method="POST"
        action="{{ route('register') }}"
        class="space-y-5"
        x-data="{ name: @js(old('name', '')), email: @js(old('email', '')), password: '', password_confirmation: '', submitting: false }"
        x-on:submit="submitting = true"
    >
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            @if ($hasSso)
                <x-text-input
                    id="name"
                    class="block mt-1.5 w-full"
                    type="text"
                    name="name"
                    x-model="name"
                    required
                    autocomplete="name"
                />
            @else
                <x-text-input
                    id="name"
                    class="block mt-1.5 w-full"
                    type="text"
                    name="name"
                    x-model="name"
                    required
                    autofocus
                    autocomplete="name"
                />
            @endif
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            @if ($hasSso)
                <x-text-input
                    id="email"
                    class="block mt-1.5 w-full"
                    type="email"
                    name="email"
                    x-model="email"
                    required
                    autofocus
                    autocomplete="username"
                />
            @else
                <x-text-input
                    id="email"
                    class="block mt-1.5 w-full"
                    type="email"
                    name="email"
                    x-model="email"
                    required
                    autocomplete="username"
                />
            @endif
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                class="block mt-1.5 w-full"
                type="password"
                name="password"
                x-model="password"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input
                id="password_confirmation"
                class="block mt-1.5 w-full"
                type="password"
                name="password_confirmation"
                x-model="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="space-y-4 pt-1">
            <x-primary-button
                class="w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-150 ease-out dark:focus:ring-offset-gray-800"
                x-bind:disabled="submitting || ! name.trim() || ! email.trim() || ! password.trim() || ! password_confirmation.trim()"
                x-bind:class="{ 'pointer-events-none': submitting }"
                x-bind:aria-busy="submitting"
            >
                <span x-show="! submitting">{{ __('Sign up') }}</span>
                <span x-show="submitting" x-cloak>{{ __('Signing up…') }}</span>
            </x-primary-button>

            @if (Route::has('login'))
                <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Already have an account?') }}
                    <a
                        href="{{ route('login') }}"
                        class="font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                    >
                        {{ __('Sign in') }}
                    </a>
                </p>
            @endif
        </div>
    </form>
</x-guest-layout>
