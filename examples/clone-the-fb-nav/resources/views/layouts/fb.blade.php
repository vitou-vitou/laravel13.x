<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Clone the FB Nav')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-fb-page text-fb-icon antialiased">
    <x-fb-top-nav />

    <main class="mx-auto max-w-[1260px] px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
