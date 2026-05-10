@extends('layouts.marketing')

@section('title', __('home.title'))

@section('content')
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('home.title') }}</h1>
    <p class="mt-4 max-w-2xl text-lg text-zinc-600">{{ __('home.lead') }}</p>

    <p class="mt-6 text-sm text-zinc-500">{{ __('home.demo_price_label') }}:
        <span class="font-mono text-base text-zinc-900">{{ \Illuminate\Support\Number::currency(1080, 'SGD', 'en_SG') }}</span>
    </p>

    <p class="mt-8">
        <a href="{{ route('services.laravel') }}" class="inline-flex rounded-md bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
            {{ __('home.cta_services') }}
        </a>
    </p>
@endsection
