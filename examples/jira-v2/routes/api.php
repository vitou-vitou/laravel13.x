<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are stateless and are typically assigned to the api middleware
| group.
|
*/

Route::post('/jira/webhook', [WebhookController::class, 'handle'])->name('jira.webhook');