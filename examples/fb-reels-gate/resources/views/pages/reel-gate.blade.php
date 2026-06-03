@extends('layouts.reels-gate')

@section('title', 'Reels — login gate study')

@section('content')
    <x-fb-reels-header />

    <div class="relative min-h-[calc(100vh-56px)]">
        <div class="px-4 py-3">
            <a href="/" class="inline-flex items-center gap-2 text-lg font-semibold text-fb-text hover:underline">
                <span aria-hidden="true" class="text-2xl leading-none text-fb-muted">&times;</span>
                <span>Reels</span>
            </a>
        </div>

        <div class="mx-auto max-w-3xl px-4 pb-24 opacity-40 blur-[1px]" aria-hidden="true">
            <div class="aspect-[9/16] max-h-[70vh] rounded-lg bg-fb-border"></div>
            <p class="mt-4 text-center text-sm text-fb-muted">Reel placeholder — content gated behind login in reference.</p>
        </div>

        <x-fb-login-gate-modal />
    </div>
@endsection
