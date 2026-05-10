@extends('layouts.marketing')

@section('title', __('privacy.title'))

@section('content')
    <h1 class="text-3xl font-bold text-gray-900">{{ __('privacy.title') }}</h1>
    <p class="mt-6 max-w-2xl text-zinc-700">{{ __('privacy.body') }}</p>
@endsection
