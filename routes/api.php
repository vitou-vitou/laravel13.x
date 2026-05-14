<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

// API Version 1 routes
Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    Route::get('todos', [TodoController::class, 'index']);
    Route::get('todos/{todo}', [TodoController::class, 'show']);

    Route::middleware(['auth:api', 'throttle:api-writes'])->group(function () {
        Route::post('todos', [TodoController::class, 'store']);
        Route::patch('todos/{todo}', [TodoController::class, 'update']);
        Route::delete('todos/{todo}', [TodoController::class, 'destroy']);
        Route::patch('todos/{todo}/complete', [TodoController::class, 'complete']);
        Route::patch('todos/{todo}/incomplete', [TodoController::class, 'incomplete']);
    });
});
