<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Week Ship Atlas')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
    <header class="border-b border-stone-300 bg-stone-50">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
            <div>
                <a href="{{ route('ship.index') }}" class="text-lg font-semibold tracking-tight">Week Ship Atlas</a>
                <p class="text-sm text-stone-600">1-week learn loop — region × habit × project type</p>
            </div>
            <a href="{{ route('ship.create') }}" class="rounded-md bg-teal-800 px-3 py-2 text-sm font-medium text-white hover:bg-teal-900">
                New note
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('ok'))
            <p class="mb-4 rounded-md border border-teal-700/30 bg-teal-50 px-3 py-2 text-sm text-teal-900">{{ session('ok') }}</p>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-900">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
