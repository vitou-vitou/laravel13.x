<x-guest-layout>
    <header class="mb-6">
        <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
            {{ __('Sign in') }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Use your email or a connected account.') }}
        </p>
    </header>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <x-auth-sso-options />

    @php($hasSso = count(app(\App\Services\SsoAuthenticator::class)->enabledProviders()) > 0)

    <form
        method="POST"
        action="{{ route('login') }}"
        class="space-y-5"
        x-data="{ email: @js(old('email', '')), password: '', showPassword: false, submitting: false }"
        x-on:submit="submitting = true"
    >
        @csrf

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
                    autofocus
                    autocomplete="username"
                />
            @endif
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" :value="__('Password')" class="mb-0" />

                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                    >
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div class="relative mt-1.5 w-full">
                <input
                    id="password"
                    class="border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-amber-400 dark:focus:ring-amber-400 rounded-md shadow-sm block w-full pe-11"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    x-model="password"
                    required
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex h-11 w-11 items-center justify-center text-gray-500 transition-colors duration-150 ease-out hover:text-gray-700 focus:outline-none focus-visible:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:focus-visible:text-gray-200"
                    x-on:click="showPassword = ! showPassword"
                    x-bind:aria-label="showPassword ? @js(__('Hide password')) : @js(__('Show password'))"
                    x-bind:aria-pressed="showPassword"
                >
                    <x-icons.eye
                        x-show="! showPassword"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="h-5 w-5"
                    />
                    <x-icons.eye-off
                        x-show="showPassword"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="h-5 w-5"
                        style="display: none;"
                    />
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="space-y-4 pt-1">
            <x-primary-button
                class="w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-150 ease-out dark:focus:ring-offset-gray-800"
                x-bind:disabled="submitting || ! email.trim() || ! password.trim()"
                x-bind:class="{ 'pointer-events-none': submitting }"
                x-bind:aria-busy="submitting"
            >
                <span x-show="! submitting">{{ __('Log in') }}</span>
                <span x-show="submitting" x-cloak>{{ __('Signing in…') }}</span>
            </x-primary-button>

            @if (Route::has('register'))
                <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                    {{ __("Don't have an account?") }}
                    <a
                        href="{{ route('register') }}"
                        class="font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                    >
                        {{ __('Sign up') }}
                    </a>
                </p>
            @endif
        </div>
    </form>
</x-guest-layout>
