<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AdjusterController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClaimController;
use App\Http\Controllers\Api\V1\DocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('otp/request', [AuthController::class, 'requestOtp']);
        Route::post('otp/verify', [AuthController::class, 'verifyOtp']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('claims')->group(function () {
            Route::post('/', [ClaimController::class, 'store']);
            Route::get('{ref}', [ClaimController::class, 'show']);
            Route::patch('{ref}/status', [ClaimController::class, 'updateStatus'])
                ->middleware('role:adjuster,admin');

            Route::prefix('{ref}/documents')->group(function () {
                Route::post('/', [DocumentController::class, 'store']);
                Route::get('{documentId}/url', [DocumentController::class, 'getUrl']);
            });
        });

        Route::middleware('role:adjuster,admin')->prefix('adjuster')->group(function () {
            Route::get('queue', [AdjusterController::class, 'queue']);
            Route::get('claims/{ref}', [AdjusterController::class, 'show']);
        });

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('tenants', [AdminController::class, 'tenants']);
            Route::post('tenants', [AdminController::class, 'storeTenant']);
            Route::patch('tenants/{tenantId}/config', [AdminController::class, 'updateTenantConfig']);

            Route::get('claims', [AdminController::class, 'claims']);
            Route::patch('claims/{ref}/assign', [AdminController::class, 'assignAdjuster']);
            Route::get('stats', [AdminController::class, 'stats']);

            Route::post('users', [AdminController::class, 'storeUser']);
            Route::delete('users/{userId}', [AdminController::class, 'deactivateUser']);
        });

    });

});
