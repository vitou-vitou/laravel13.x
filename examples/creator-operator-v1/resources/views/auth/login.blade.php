<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @php
        $devLogin = config('dev-login');
        $devEnabled = (bool) ($devLogin['enabled'] ?? false);
        $devEmail = $devEnabled ? (string) ($devLogin['email'] ?? '') : '';
        $devPassword = $devEnabled ? (string) ($devLogin['password'] ?? '') : '';
    @endphp

    @if ($devEnabled)
        <div class="ops-callout-dev mb-6">
            <p class="font-semibold">Development login</p>
            <p class="mt-1 text-xs text-amber-800/90">Credentials are prefilled. Password is <code class="rounded bg-amber-100/80 px-1">password</code> for seeded users.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($devLogin['accounts'] ?? [] as $account)
                    <button
                        type="button"
                        class="ops-chip-inactive cursor-pointer"
                        onclick="document.getElementById('email').value = @js($account['email']); document.getElementById('password').value = @js($devPassword);"
                    >
                        {{ $account['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email', $devEmail)"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                :value="$devEnabled ? $devPassword : ''"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-stone-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a class="text-sm text-stone-600 hover:text-stone-900 underline-offset-2 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-auto">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
