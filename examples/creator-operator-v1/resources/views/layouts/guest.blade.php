<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Creator Operator') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="ops-guest-shell">
            <div class="text-center px-4">
                <a href="/" class="inline-block">
                    <p class="ops-guest-brand">{{ config('app.name') }}</p>
                    <p class="ops-guest-tagline">TikTok-first cross-post operations</p>
                </a>
            </div>

            <div class="ops-guest-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
