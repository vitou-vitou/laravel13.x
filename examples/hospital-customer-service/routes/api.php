<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportStatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/faq/tree', [FaqController::class, 'tree']);
    Route::get('/faq/search', [FaqController::class, 'search']);

    Route::post('/queue/tokens', [QueueController::class, 'issue']);
    Route::get('/queue/tokens/{token_number}', [QueueController::class, 'show']);

    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{tracking_code}', [AppointmentController::class, 'show']);

    Route::post('/reports/check', [ReportStatusController::class, 'check']);

    Route::prefix('staff')->group(function () {
        Route::get('/queue', [QueueController::class, 'staffQueue']);
        Route::post('/queue/{id}/call', [QueueController::class, 'callNext']);
        Route::post('/queue/{id}/status', [QueueController::class, 'updateStatus']);

        Route::get('/appointments', [AppointmentController::class, 'staffIndex']);
        Route::post('/appointments/{id}/confirm', [AppointmentController::class, 'confirm']);
        Route::post('/appointments/{id}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);

        Route::get('/reports', [ReportStatusController::class, 'staffIndex']);
        Route::post('/reports', [ReportStatusController::class, 'staffStore']);
        Route::put('/reports/{id}', [ReportStatusController::class, 'staffUpdate']);
    });

    Route::prefix('admin')->group(function () {
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{id}', [DepartmentController::class, 'update']);

        Route::post('/faqs', [FaqController::class, 'store']);
        Route::put('/faqs/{id}', [FaqController::class, 'update']);
        Route::delete('/faqs/{id}', [FaqController::class, 'destroy']);
    });
});
