<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\AttachmentMail;
use App\Mail\AttachmentMailQueued;
use App\Mail\WelcomeMail;
use App\Mail\WelcomeMailQueued;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

class MailController extends Controller
{
    /**
     * Send welcome email via Laravel Mail (uses configured mailer — SMTP or log).
     */
    public function sendViaLaravel(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        Mail::to($request->email)->send(
            new WelcomeMail($request->name)
        );

        return response()->json(['message' => 'Email sent via Laravel mailer.']);
    }

    /**
     * Send welcome email via Mailtrap SDK (Email Sending API).
     */
    public function sendViaMailtrapApi(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        $mailtrap = MailtrapClient::initSendingEmails(
            apiKey: config('services.mailtrap.api_token')
        );

        $email = (new MailtrapEmail())
            ->from(new Address(config('mail.from.address'), config('mail.from.name')))
            ->to(new Address($request->email, $request->name))
            ->subject('Welcome to ' . config('app.name'))
            ->text("Welcome, {$request->name}! Thanks for joining " . config('app.name'))
            ->html("<h1>Welcome, {$request->name}!</h1><p>Thanks for joining <strong>" . config('app.name') . '</strong>.</p>');

        $response = $mailtrap->send($email);

        return response()->json([
            'message'   => 'Email sent via Mailtrap API.',
            'messageId' => ResponseHelper::toArray($response)['message_ids'][0] ?? null,
        ]);
    }

    /**
     * Send to Mailtrap sandbox (testing inbox) via SMTP.
     */
    public function sendToSandbox(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        Mail::mailer('mailtrap')->to($request->email)->send(
            new WelcomeMail($request->name)
        );

        return response()->json(['message' => 'Email sent to Mailtrap sandbox.']);
    }

    /**
     * Dispatch welcome email to the queue (async, non-blocking).
     */
    public function sendQueued(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        Mail::to($request->email)->queue(
            new WelcomeMailQueued($request->name, $request->email)
        );

        return response()->json(['message' => 'Email queued for delivery.']);
    }

    /**
     * Send email with a file attachment.
     */
    public function sendWithAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email',
            'attachment' => 'required|file|max:10240',
        ]);

        $file = $request->file('attachment');
        $path = $file->store('mail-attachments', 'local');

        Mail::to($request->email)->send(
            new AttachmentMail(
                userName:       $request->name,
                attachmentPath: storage_path("app/private/{$path}"),
                attachmentName: $file->getClientOriginalName(),
            )
        );

        return response()->json(['message' => 'Email with attachment sent.']);
    }

    /**
     * Queue email with a file attachment (store file first, worker sends async).
     */
    public function sendQueuedWithAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email',
            'attachment' => 'required|file|max:10240',
        ]);

        $file        = $request->file('attachment');
        $storagePath = $file->store('mail-attachments', 'local');

        Mail::to($request->email)->queue(
            new AttachmentMailQueued(
                userName:       $request->name,
                userEmail:      $request->email,
                storagePath:    $storagePath,
                attachmentName: $file->getClientOriginalName(),
            )
        );

        return response()->json(['message' => 'Email with attachment queued for delivery.']);
    }

    /**
     * Send a Laravel Notification via mail channel (on-demand, no User model required).
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        Notification::route('mail', $request->email)
            ->notify(new WelcomeNotification($request->name));

        return response()->json(['message' => 'Notification dispatched via mail channel.']);
    }
}
