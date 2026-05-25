<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailtrapWebhookController extends Controller
{
    /**
     * Receive and process Mailtrap webhook events.
     *
     * Configure your webhook URL in Mailtrap dashboard → Email Sending → Webhooks.
     * Events: delivery, open, click, bounce, spam, unsubscribe.
     *
     * Mailtrap signs each request with HMAC-SHA256 using your webhook secret.
     * Set MAILTRAP_WEBHOOK_SECRET in .env to enable signature verification.
     */
    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $events = $request->input('events', []);

        foreach ($events as $event) {
            $this->processEvent($event);
        }

        return response()->json(['message' => 'OK']);
    }

    private function verifySignature(Request $request): bool
    {
        $secret = config('services.mailtrap.webhook_secret');

        // Skip verification if no secret configured (dev/testing only)
        if (empty($secret)) {
            return true;
        }

        $signature = $request->header('X-Mailtrap-Signature');
        if (empty($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    private function processEvent(array $event): void
    {
        $type      = $event['event']      ?? 'unknown';
        $email     = $event['email']      ?? null;
        $messageId = $event['message_id'] ?? null;
        $timestamp = $event['timestamp']  ?? null;

        Log::info('Mailtrap webhook event', [
            'type'       => $type,
            'email'      => $email,
            'message_id' => $messageId,
            'timestamp'  => $timestamp,
        ]);

        match ($type) {
            'delivery'    => $this->onDelivery($event),
            'open'        => $this->onOpen($event),
            'click'       => $this->onClick($event),
            'bounce'      => $this->onBounce($event),
            'spam'        => $this->onSpam($event),
            'unsubscribe' => $this->onUnsubscribe($event),
            default       => null,
        };
    }

    private function onDelivery(array $event): void
    {
        // Email delivered — update send status in DB, trigger follow-up logic
        Log::info('Email delivered', ['email' => $event['email'] ?? null]);
    }

    private function onOpen(array $event): void
    {
        // Email opened — track engagement
        Log::info('Email opened', ['email' => $event['email'] ?? null]);
    }

    private function onClick(array $event): void
    {
        // Link clicked — track which URL was clicked
        Log::info('Email link clicked', [
            'email' => $event['email'] ?? null,
            'url'   => $event['url']   ?? null,
        ]);
    }

    private function onBounce(array $event): void
    {
        // Bounced — mark address as invalid, remove from list
        Log::warning('Email bounced', [
            'email'        => $event['email']        ?? null,
            'bounce_type'  => $event['bounce_type']  ?? null,
            'bounce_code'  => $event['bounce_code']  ?? null,
        ]);
    }

    private function onSpam(array $event): void
    {
        // Spam complaint — suppress address immediately
        Log::warning('Email marked as spam', ['email' => $event['email'] ?? null]);
    }

    private function onUnsubscribe(array $event): void
    {
        // Unsubscribe — honor immediately to stay CAN-SPAM/GDPR compliant
        Log::info('Email unsubscribed', ['email' => $event['email'] ?? null]);
    }
}
