<?php

use App\Http\Controllers\MailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/signing', function () {
//    signing
    return response()->json([
        'url' => URL::signedRoute('email.action', ['user' => 123])
    ]);
});

Route::get('/action', function (Request $request) {
    if (!$request->hasValidSignature()) {
        abort(403);
    }

    // safe to proceed
    return response()->json([
        'message' => 'Signed route found'
    ]);

})->name('email.action');

// Mailtrap demo routes
Route::prefix('mail')->group(function () {
    // Send via Laravel Mail (SMTP/log driver from .env)
    Route::post('/send', [MailController::class, 'sendViaLaravel'])->name('mail.send');

    // Send via Mailtrap Sending API (requires MAILTRAP_API_TOKEN)
    Route::post('/send-api', [MailController::class, 'sendViaMailtrapApi'])->name('mail.send-api');

    // Send to Mailtrap sandbox inbox (testing)
    Route::post('/sandbox', [MailController::class, 'sendToSandbox'])->name('mail.sandbox');

    // Dispatch email to queue (async, non-blocking)
    Route::post('/send-queued', [MailController::class, 'sendQueued'])->name('mail.send-queued');

    // Send email with file attachment (multipart/form-data)
    Route::post('/send-attachment', [MailController::class, 'sendWithAttachment'])->name('mail.send-attachment');

    // Send via Laravel Notification (mail channel)
    Route::post('/notify', [MailController::class, 'sendNotification'])->name('mail.notify');
});

