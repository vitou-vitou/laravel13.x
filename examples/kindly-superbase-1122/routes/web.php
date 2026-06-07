<?php

use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\SupabaseHealthCheckController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class);
Route::get('/activity-logs', ActivityLogsController::class)->name('activity-logs.index');
Route::post('/supabase/health-check', SupabaseHealthCheckController::class)->name('supabase.health-check');
