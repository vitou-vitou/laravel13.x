<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MailtrapWebhookControllerTest extends TestCase
{
    private string $webhookUrl = '/webhooks/mailtrap';

    private function makePayload(string $event, array $extra = []): array
    {
        return [
            'events' => [
                array_merge([
                    'event'      => $event,
                    'email'      => 'user@example.com',
                    'message_id' => 'msg_abc123',
                    'timestamp'  => 1716400000,
                ], $extra),
            ],
        ];
    }

    private function sign(string $body, string $secret): string
    {
        return hash_hmac('sha256', $body, $secret);
    }

    // ─── Signature verification ───────────────────────────────────────────────

    public function test_returns_401_when_secret_configured_but_no_signature(): void
    {
        config(['services.mailtrap.webhook_secret' => 'secret123']);

        $this->postJson($this->webhookUrl, $this->makePayload('delivery'))
            ->assertStatus(401);
    }

    public function test_returns_401_when_signature_is_invalid(): void
    {
        config(['services.mailtrap.webhook_secret' => 'secret123']);

        $this->postJson($this->webhookUrl, $this->makePayload('delivery'), [
            'X-Mailtrap-Signature' => 'bad_signature',
        ])->assertStatus(401);
    }

    public function test_returns_200_when_signature_is_valid(): void
    {
        $secret  = 'secret123';
        $payload = $this->makePayload('delivery');
        $body    = json_encode($payload);

        config(['services.mailtrap.webhook_secret' => $secret]);

        $this->postJson($this->webhookUrl, $payload, [
            'X-Mailtrap-Signature' => $this->sign($body, $secret),
        ])->assertStatus(200)->assertJson(['message' => 'OK']);
    }

    public function test_returns_200_without_secret_configured(): void
    {
        config(['services.mailtrap.webhook_secret' => null]);

        $this->postJson($this->webhookUrl, $this->makePayload('delivery'))
            ->assertStatus(200)
            ->assertJson(['message' => 'OK']);
    }

    // ─── All event types accepted ─────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\DataProvider('eventTypeProvider')]
    public function test_accepts_all_event_types(string $event): void
    {
        config(['services.mailtrap.webhook_secret' => null]);

        $this->postJson($this->webhookUrl, $this->makePayload($event))
            ->assertStatus(200);
    }

    public static function eventTypeProvider(): array
    {
        return [
            'delivery'    => ['delivery'],
            'open'        => ['open'],
            'click'       => ['click'],
            'bounce'      => ['bounce'],
            'spam'        => ['spam'],
            'unsubscribe' => ['unsubscribe'],
            'unknown'     => ['some_unknown_event'],
        ];
    }

    // ─── Click event carries URL ──────────────────────────────────────────────

    public function test_click_event_payload_with_url(): void
    {
        config(['services.mailtrap.webhook_secret' => null]);

        $payload = $this->makePayload('click', ['url' => 'https://example.com/cta']);

        $this->postJson($this->webhookUrl, $payload)
            ->assertStatus(200);
    }

    // ─── Bounce event carries bounce metadata ─────────────────────────────────

    public function test_bounce_event_payload_with_metadata(): void
    {
        config(['services.mailtrap.webhook_secret' => null]);

        $payload = $this->makePayload('bounce', [
            'bounce_type' => 'hard',
            'bounce_code' => '550',
        ]);

        $this->postJson($this->webhookUrl, $payload)
            ->assertStatus(200);
    }

    // ─── Empty events array ───────────────────────────────────────────────────

    public function test_empty_events_array_returns_200(): void
    {
        config(['services.mailtrap.webhook_secret' => null]);

        $this->postJson($this->webhookUrl, ['events' => []])
            ->assertStatus(200);
    }

    // ─── Multiple events in one request ──────────────────────────────────────

    public function test_multiple_events_processed(): void
    {
        config(['services.mailtrap.webhook_secret' => null]);

        $payload = [
            'events' => [
                ['event' => 'delivery', 'email' => 'a@example.com', 'message_id' => 'id1', 'timestamp' => 1716400001],
                ['event' => 'open',     'email' => 'b@example.com', 'message_id' => 'id2', 'timestamp' => 1716400002],
            ],
        ];

        $this->postJson($this->webhookUrl, $payload)
            ->assertStatus(200);
    }
}
