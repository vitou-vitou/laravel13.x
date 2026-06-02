<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('shop.index') }}" class="text-lg font-semibold">Kindly E-Commerce</a>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('shop.index') }}" class="hover:underline">Shop</a>
                    <a href="{{ route('cart.index') }}" class="hover:underline">Cart</a>
                    @auth
                        <a href="{{ route('orders.index') }}" class="hover:underline">Orders</a>
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.products.index') }}" class="hover:underline">Admin</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="hover:underline">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:underline">Log in</a>
                        <a href="{{ route('register') }}" class="hover:underline">Register</a>
                    @endauth
                </div>
            </div>
        </nav>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 text-green-800 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 text-red-800 px-4 py-3 text-sm">
                    <ul class="list-disc list-inside">
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
