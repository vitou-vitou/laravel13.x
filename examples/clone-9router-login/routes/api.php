<?php

use App\Http\Controllers\Auth\DashboardAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [DashboardAuthController::class, 'login']);
    Route::get('status', [DashboardAuthController::class, 'status']);
    Route::post('logout', [DashboardAuthController::class, 'logout']);
});
