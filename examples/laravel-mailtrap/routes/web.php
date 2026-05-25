<?php

use App\Http\Controllers\MailController;
use App\Http\Controllers\MailtrapWebhookController;
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

// -----------------------------------------------------------------------------
// WHY MAILTRAP?
//
// Mailtrap solves the two hardest problems in email development:
//
//   1. SAFE TESTING — Sandbox inbox captures all outgoing emails so they
//      never reach real users during development/staging. No accidental
//      spam, no test emails leaking to customers.
//
//   2. RELIABLE SENDING — Sending API (send.api.mailtrap.io) handles
//      deliverability, bounce tracking, open/click analytics, and
//      suppression lists out of the box. No managing your own MTA.
//
// Compared to plain SMTP:
//   ✓ No blacklist risk — dedicated IPs with maintained reputation
//   ✓ Built-in delivery dashboard and HTML/text email previews
//   ✓ Instant sandbox — no local mail server setup needed
//   ✓ Same SDK covers both testing (sandbox) and production (sending API)
//   ✓ Spam score analysis and HTML compatibility checks per email
// -----------------------------------------------------------------------------
//
// Mailtrap demo routes — each route demonstrates a different sending strategy.
// All routes expect JSON body: { "name": "...", "email": "..." }
// except /send-attachment which expects multipart/form-data with an "attachment" file field.
// WHAT: Receive Mailtrap webhook events (delivery, open, click, bounce, spam, unsubscribe).
// WHY:  Allows tracking email lifecycle — update DB on delivery, suppress bounced/spam addresses.
//       Excluded from CSRF middleware (external POST from Mailtrap servers).
//       Set MAILTRAP_WEBHOOK_SECRET in .env — verified via HMAC-SHA256 X-Mailtrap-Signature header.
Route::post('/webhooks/mailtrap', [MailtrapWebhookController::class, 'handle'])
    ->name('webhooks.mailtrap')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::prefix('mail')->group(function () {
    // WHAT: Send email synchronously via the mailer configured in .env (MAIL_MAILER).
    // WHY:  Simplest integration — use log driver locally, swap to real SMTP in prod with zero code change.
    Route::post('/send', [MailController::class, 'sendViaLaravel'])->name('mail.send');

    // WHAT: Send email via Mailtrap's HTTP Sending API using railsware/mailtrap-php SDK.
    // WHY:  Bypasses SMTP entirely — faster, reliable delivery tracking, requires MAILTRAP_API_TOKEN.
    Route::post('/send-api', [MailController::class, 'sendViaMailtrapApi'])->name('mail.send-api');

    // WHAT: Send email to Mailtrap's sandbox inbox via SMTP (uses 'mailtrap' mailer from config/mail.php).
    // WHY:  Safe testing environment — emails are captured, never reach real recipients. Requires MAILTRAP_USERNAME + MAILTRAP_PASSWORD.
    Route::post('/sandbox', [MailController::class, 'sendToSandbox'])->name('mail.sandbox');

    // WHAT: Push email onto the queue instead of sending inline.
    // WHY:  Keeps HTTP response fast — delivery happens asynchronously via queue worker. Uses WelcomeMailQueued (ShouldQueue).
    Route::post('/send-queued', [MailController::class, 'sendQueued'])->name('mail.send-queued');

    // WHAT: Send email with an uploaded file attached. Accepts multipart/form-data with an "attachment" field (max 10 MB).
    // WHY:  Demonstrates Laravel's Attachment::fromPath() — covers the common "send document to user" pattern.
    Route::post('/send-attachment', [MailController::class, 'sendWithAttachment'])->name('mail.send-attachment');

    // WHAT: Queue email with attachment — stores file first, worker sends async via Attachment::fromStorageDisk().
    // WHY:  UploadedFile can't serialize to queue. Storing first lets the worker read from disk safely.
    Route::post('/send-queued-attachment', [MailController::class, 'sendQueuedWithAttachment'])->name('mail.send-queued-attachment');

    // WHAT: Dispatch a Laravel Notification via the mail channel (on-demand, no User model required).
    // WHY:  Notifications are transport-agnostic — swap mail for Slack/SMS by changing via(). Best for system events.
    Route::post('/notify', [MailController::class, 'sendNotification'])->name('mail.notify');

    // WHAT: Send same email to up to 1000 recipients in one API call via Mailtrap Bulk Sending API.
    // WHY:  Bulk endpoint (bulk.api.mailtrap.io) is purpose-built for newsletters/broadcasts — higher throughput,
    //       separate sending reputation from transactional emails. Requires MAILTRAP_API_TOKEN.
    //       Body: { "subject": "...", "html": "...", "recipients": [{"name":"...","email":"..."}] }
    Route::post('/send-bulk', [MailController::class, 'sendBulk'])->name('mail.send-bulk');
});

