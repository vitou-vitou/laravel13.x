<x-guest-layout>
    <header class="mb-6">
        <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
            {{ __('Reset password') }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Enter your email and we will send you a reset link.') }}
        </p>
    </header>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form
        method="POST"
        action="{{ route('password.email') }}"
        class="space-y-5"
        x-data="{ email: @js(old('email', '')), submitting: false }"
        x-on:submit="submitting = true"
    >
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
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
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="space-y-4 pt-1">
            <x-primary-button
                class="w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-150 ease-out dark:focus:ring-offset-gray-800"
                x-bind:disabled="submitting || ! email.trim()"
                x-bind:class="{ 'pointer-events-none': submitting }"
                x-bind:aria-busy="submitting"
            >
                <span x-show="! submitting">{{ __('Email reset link') }}</span>
                <span x-show="submitting" x-cloak>{{ __('Sending link…') }}</span>
            </x-primary-button>

            @if (Route::has('login'))
                <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                    <a
                        href="{{ route('login') }}"
                        class="font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                    >
                        {{ __('Back to sign in') }}
                    </a>
                </p>
            @endif
        </div>
    </form>
</x-guest-layout>
