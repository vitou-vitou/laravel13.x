<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-stone-900">
        <div class="flex min-h-screen flex-col items-center justify-center bg-stone-50 px-4 py-10 sm:px-6">
            <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white">M</span>
                <span class="text-lg font-semibold text-stone-900">{{ config('app.name') }}</span>
            </a>

            <div class="w-full max-w-md store-panel">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
