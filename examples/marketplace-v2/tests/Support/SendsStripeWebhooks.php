<?php

namespace Tests\Support;

trait SendsStripeWebhooks
{
    protected function postStripeWebhook(array $eventPayload): \Illuminate\Testing\TestResponse
    {
        $payload = json_encode($eventPayload);
        $signature = $this->stripeSignatureHeader($payload);

        return $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        );
    }

    protected function stripeSignatureHeader(string $payload, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $signedPayload = $timestamp.'.'.$payload;
        $signature = hash_hmac('sha256', $signedPayload, (string) config('stripe.webhook_secret'));

        return "t={$timestamp},v1={$signature}";
    }
}
