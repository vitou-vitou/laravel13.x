<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('orders/create', 'orders.create')->name('orders.create');
    Route::get('orders/{order}', fn (\App\Models\Order $order) => view('orders.show', compact('order')))->name('orders.show');
});

require __DIR__.'/settings.php';
