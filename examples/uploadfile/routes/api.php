<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::prefix('files')->group(function () {
    Route::post('/', [FileController::class, 'store']);
    Route::get('/', [FileController::class, 'index']);
    Route::get('/{filename}', [FileController::class, 'download']);
    Route::delete('/{filename}', [FileController::class, 'destroy']);
});
