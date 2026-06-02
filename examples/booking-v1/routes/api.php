<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\SlotController;
use App\Http\Controllers\Provider\SetupController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/provider/setup', [SetupController::class, 'storeService'])->middleware('provider');
    Route::get('/services/{service}/slots', SlotController::class);
    Route::post('/services/{service}/bookings/hold', [BookingController::class, 'hold']);
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::get('/my/bookings', [BookingController::class, 'mine']);
});
