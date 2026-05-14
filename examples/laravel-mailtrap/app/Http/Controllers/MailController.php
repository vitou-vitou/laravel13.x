<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
}
