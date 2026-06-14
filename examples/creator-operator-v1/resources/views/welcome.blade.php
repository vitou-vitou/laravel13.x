<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Creator Commission ops</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4 sm:px-6">
            <span class="font-semibold">{{ config('app.name') }}</span>
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="rounded-md bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-800">Log in</a>
            @endif
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-semibold tracking-tight">TikTok-first cross-post operations</h1>
        <p class="mt-3 max-w-2xl text-stone-600">
            You keep creating on TikTok. Your operator packages titles and metadata for YouTube Shorts and Instagram Reels —
            you approve everything before anything goes live.
        </p>

        <div class="mt-10 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-medium text-stone-900">Operator</h2>
                <p class="mt-2 text-sm text-stone-600">Weekly batch queue, publish log, onboard creators. Maps to the pilot checklist steps 1–6.</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-medium text-stone-900">Creator</h2>
                <p class="mt-2 text-sm text-stone-600">Approval inbox only — review proposed titles, open TikTok source, approve or skip within 24–48h.</p>
            </div>
        </div>

        <p class="mt-8 text-sm text-stone-500">
            Spec: <code class="rounded bg-stone-100 px-1">docs/superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md</code>
            · UI map: <code class="rounded bg-stone-100 px-1">examples/creator-operator-v1/docs/DESIGN.md</code>
        </p>
    </main>
</body>
</html>
