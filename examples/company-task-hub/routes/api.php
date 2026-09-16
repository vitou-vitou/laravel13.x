<?php

use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Version 1 routes
Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    // Read operations (higher rate limit)
    Route::get('todos', [TodoController::class, 'index']);
    Route::get('todos/{todo}', [TodoController::class, 'show']);

    // Write operations (lower rate limit)
    Route::middleware(['throttle:api-writes'])->group(function () {
        Route::post('todos', [TodoController::class, 'store']);
        Route::patch('todos/{todo}', [TodoController::class, 'update']);
        Route::delete('todos/{todo}', [TodoController::class, 'destroy']);
        Route::patch('todos/{todo}/complete', [TodoController::class, 'complete']);
        Route::patch('todos/{todo}/incomplete', [TodoController::class, 'incomplete']);
    });
});