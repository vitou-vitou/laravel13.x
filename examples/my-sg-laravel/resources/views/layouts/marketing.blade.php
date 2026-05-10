<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name'))</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('home') }}" class="font-semibold text-gray-900">{{ config('app.name') }}</a>
                <nav class="flex flex-wrap items-center gap-4 text-sm text-zinc-700">
                    <a href="{{ route('home') }}" class="hover:underline">{{ __('nav.home') }}</a>
                    <a href="{{ route('services.laravel') }}" class="hover:underline">{{ __('nav.services_laravel') }}</a>
                    <a href="{{ route('privacy') }}" class="hover:underline">{{ __('nav.privacy') }}</a>
                    <span class="text-zinc-400">|</span>
                    <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="{{ app()->isLocale('en') ? 'font-semibold text-gray-900' : 'hover:underline' }}">{{ __('nav.locale.en') }}</a>
                    <a href="{{ route('locale.switch', ['locale' => 'zh_CN']) }}" class="{{ app()->isLocale('zh_CN') ? 'font-semibold text-gray-900' : 'hover:underline' }}">{{ __('nav.locale.zh') }}</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-10">
            @yield('content')
        </main>

        <x-cookie-banner />

        @if (Route::has('login'))
            <footer class="border-t border-zinc-200 bg-white py-6 text-center text-xs text-zinc-500">
                <a href="{{ route('login') }}" class="underline">{{ __('nav.login') }}</a>
                @if (Route::has('register'))
                    <span class="mx-2">·</span>
                    <a href="{{ route('register') }}" class="underline">{{ __('nav.register') }}</a>
                @endif
            </footer>
        @endif
    </body>
</html>
