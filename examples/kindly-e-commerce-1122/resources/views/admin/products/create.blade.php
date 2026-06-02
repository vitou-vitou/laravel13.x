@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-6">New product</h1>
    <form method="POST" action="{{ route('admin.products.store') }}" class="bg-white rounded-lg shadow p-6 max-w-lg">
        @csrf
        @include('admin.products._form', ['product' => null])
        <button type="submit" class="mt-6 bg-gray-800 text-white rounded-md px-6 py-2 text-sm">Create</button>
    </form>
@endsection
