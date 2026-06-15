<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Creator Commission ops</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans min-h-screen bg-stone-50 text-stone-900 antialiased">
    <header class="border-b border-stone-200/80 bg-white/95 backdrop-blur-sm sticky top-0 z-10">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3.5 sm:px-6">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-stone-900 text-xs font-bold text-white">CO</span>
                <span class="font-semibold tracking-tight">{{ config('app.name') }}</span>
            </div>
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="ops-btn-primary ops-btn-sm">Log in</a>
            @endif
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 sm:py-16">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">TikTok-first ops portal</p>
            <h1 class="mt-2 text-3xl sm:text-4xl font-semibold tracking-tight text-stone-900">Cross-post operations without losing creator trust</h1>
            <p class="mt-4 text-lg text-stone-600 leading-relaxed">
                You keep creating on TikTok. Your operator packages titles and metadata for YouTube Shorts and Instagram Reels —
                you approve everything before anything goes live.
            </p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2">
            <div class="ops-panel ops-panel-body">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-stone-900 text-white text-sm font-bold mb-3">Op</div>
                <h2 class="font-semibold text-stone-900">Operator</h2>
                <p class="mt-2 text-sm text-stone-600 leading-relaxed">Weekly batch queue, publish log, creator onboarding. Maps to pilot checklist steps 1–6.</p>
            </div>
            <div class="ops-panel ops-panel-body">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white text-sm font-bold mb-3">Cr</div>
                <h2 class="font-semibold text-stone-900">Creator</h2>
                <p class="mt-2 text-sm text-stone-600 leading-relaxed">Approval inbox only — review proposed titles, open TikTok source, approve or skip within 24–48h.</p>
            </div>
        </div>

        <div class="mt-10 ops-panel ops-panel-body">
            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Weekly batch loop</p>
            <ol class="grid gap-2 sm:grid-cols-7 text-center text-[11px]">
                @foreach (['Build list', 'Eligibility', 'Asset', 'Packaging', 'Approval', 'Publish', 'Report'] as $i => $label)
                    <li class="rounded-lg border border-stone-200 bg-white px-2 py-2 font-medium text-stone-700">{{ $i + 1 }}. {{ $label }}</li>
                @endforeach
            </ol>
        </div>

        <div class="mt-10 flex flex-wrap items-center gap-4">
            <a href="{{ route('login') }}" class="ops-btn-primary">Sign in to your portal</a>
            <p class="text-sm text-stone-500">Operator batch queue or creator approval inbox</p>
        </div>

        <p class="mt-8 text-xs text-stone-400">
            Weekly batch ops for TikTok-first creators — cross-post to Shorts and Reels with creator approval.
        </p>
    </main>
</body>
</html>
