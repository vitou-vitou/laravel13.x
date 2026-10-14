<?php

use App\Http\Controllers\Auth\DashboardAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [DashboardAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [DashboardAuthController::class, 'login']);
Route::post('/logout', [DashboardAuthController::class, 'logout'])->name('logout');

Route::middleware('dashboard.auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
