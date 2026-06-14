<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @php
        $devLogin = config('dev-login');
        $devEnabled = (bool) ($devLogin['enabled'] ?? false);
        $devEmail = $devEnabled ? (string) ($devLogin['email'] ?? '') : '';
        $devPassword = $devEnabled ? (string) ($devLogin['password'] ?? '') : '';
    @endphp

    @if ($devEnabled)
        <div
            class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950"
            x-data="{
                email: @js(old('email', $devEmail)),
                password: @js($devPassword),
                pick(accountEmail) {
                    this.email = accountEmail;
                    this.password = @js($devPassword);
                },
            }"
        >
            <p class="font-semibold">Development login</p>
            <p class="mt-1 text-xs text-amber-800">Credentials are prefilled. Password is <code class="rounded bg-amber-100 px-1">password</code> for seeded users.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($devLogin['accounts'] ?? [] as $account)
                    <button
                        type="button"
                        class="rounded-full border border-amber-300 bg-white px-3 py-1 text-xs font-medium text-amber-900 hover:bg-amber-100"
                        @click="pick(@js($account['email']))"
                    >
                        {{ $account['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email', $devEmail)"
                @if ($devEnabled) x-model="email" @endif
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                @if ($devEnabled) x-bind:value="password" x-model="password" @else value="" @endif
                required
                autocomplete="current-password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-brand-600 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-stone-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="link-brand text-sm" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
