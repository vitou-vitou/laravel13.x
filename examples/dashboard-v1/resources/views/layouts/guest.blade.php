<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('partials.theme-init')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased dark:text-gray-100">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-14 pb-10 sm:pt-0 sm:pb-0 bg-gray-100 dark:bg-gray-900 px-4 sm:px-6">
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-10">
                <x-theme-toggle />
            </div>

            <x-application-brand class="mb-1" />

            <div class="w-full sm:max-w-md mt-5 sm:mt-6 px-6 py-8 sm:px-8 bg-white dark:bg-gray-800 shadow-md dark:shadow-gray-950/40 ring-1 ring-gray-900/5 dark:ring-white/10 overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
