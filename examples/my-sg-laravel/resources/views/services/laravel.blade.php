@extends('layouts.marketing')

@section('title', __('services.title'))

@section('content')
    <h1 class="text-3xl font-bold text-gray-900">{{ __('services.title') }}</h1>
    <p class="mt-4 max-w-2xl text-zinc-600">{{ __('services.lead') }}</p>

    <h2 class="mt-10 text-xl font-semibold text-gray-900">{{ __('services.benefits_title') }}</h2>
    <ul class="mt-4 list-disc space-y-2 pl-6 text-zinc-700">
        <li>{{ __('services.benefit.performance') }}</li>
        <li>{{ __('services.benefit.security') }}</li>
        <li>{{ __('services.benefit.scale') }}</li>
        <li>{{ __('services.benefit.velocity') }}</li>
    </ul>

    <h2 class="mt-10 text-xl font-semibold text-gray-900">{{ __('services.industries_title') }}</h2>
    <ul class="mt-4 list-disc space-y-2 pl-6 text-zinc-700">
        <li>{{ __('services.industry.fintech') }}</li>
        <li>{{ __('services.industry.healthcare') }}</li>
        <li>{{ __('services.industry.saas') }}</li>
    </ul>
@endsection
