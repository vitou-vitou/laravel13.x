<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <x-auth-sso-options />

    @php($hasSso = count(app(\App\Services\SsoAuthenticator::class)->enabledProviders()) > 0)

    <form
        method="POST"
        action="{{ route('login') }}"
        x-data="{ email: @js(old('email', '')), password: '', showPassword: false, submitting: false }"
        x-on:submit="submitting = true"
    >
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            @if ($hasSso)
                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    x-model="email"
                    required
                    autocomplete="username"
                />
            @else
                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
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

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1 w-full">
                <input
                    id="password"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full pe-10"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    x-model="password"
                    required
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center pe-3 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700"
                    x-on:click="showPassword = ! showPassword"
                    x-bind:aria-label="showPassword ? @js(__('Hide password')) : @js(__('Show password'))"
                >
                    <x-icons.eye
                        x-show="! showPassword"
                        x-cloak
                        x-transition:enter="transition ease-out duration-[120ms]"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-[120ms]"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="h-5 w-5 block"
                    />
                    <x-icons.eye-off
                        x-show="showPassword"
                        x-cloak
                        x-transition:enter="transition ease-out duration-[120ms]"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-[120ms]"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="h-5 w-5 block"
                        style="display: none;"
                    />
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-primary-button
                class="w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-opacity duration-150 ease-out"
                x-bind:disabled="submitting || ! email.trim() || ! password.trim()"
                x-bind:class="{ 'pointer-events-none': submitting }"
            >
                {{ __('Log in') }}
            </x-primary-button>

            <div class="mt-3 space-y-1">
                @if (Route::has('register'))
                    <p class="text-sm text-center text-gray-600">
                        {{ __("Don't have an account?") }}
                        <a class="text-blue-600 no-underline hover:no-underline hover:text-blue-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{ route('register') }}">{{ __('Sign up') }}</a>
                    </p>
                @endif

                @if (Route::has('password.request'))
                    <p class="text-sm text-center text-gray-600">
                        {{ __('Forgot your') }}
                        <a class="font-medium text-blue-600 no-underline hover:no-underline hover:text-blue-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{ route('password.request') }}">{{ __('password?') }}</a>
                    </p>
                @endif
            </div>
        </div>
    </form>
</x-guest-layout>
