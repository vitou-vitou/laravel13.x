@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Edit {{ $product->name }}</h1>
    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="bg-white rounded-lg shadow p-6 max-w-lg">
        @csrf
        @method('PUT')
        @include('admin.products._form', ['product' => $product])
        <button type="submit" class="mt-6 bg-gray-800 text-white rounded-md px-6 py-2 text-sm">Save</button>
    </form>
@endsection
