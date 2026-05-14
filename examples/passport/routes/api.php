<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('v1')->group(function () {
    Route::get('todos', [TodoController::class, 'index']);

    Route::middleware('auth:api')->group(function () {
        Route::post('todos', [TodoController::class, 'store']);
    });
});
