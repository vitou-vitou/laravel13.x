<?php

namespace App\Services;

use App\Enums\IntegrationEvent;
use App\Models\IntegrationWebhook;
use App\Models\IntegrationWebhookDelivery;
use App\Models\PublishLogEntry;
use Illuminate\Support\Facades\Http;

class IntegrationWebhookDispatcher
{
    public function dispatchForEntry(IntegrationEvent $event, PublishLogEntry $entry): void
    {
        $entry->load('creator');

        $webhooks = IntegrationWebhook::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (IntegrationWebhook $hook) => $this->hookListens($hook, $event));

        if ($webhooks->isEmpty()) {
            return;
        }

        $payload = [
            'event' => $event->value,
            'entry_id' => $entry->id,
            'creator_id' => $entry->creator_id,
            'creator_handle' => $entry->creator->handle,
            'status' => $entry->status->value,
            'tiktok_url' => $entry->tiktok_url,
            'yt_url' => $entry->yt_url,
            'ig_url' => $entry->ig_url,
            'logged_on' => $entry->logged_on->toDateString(),
            'occurred_at' => now()->toIso8601String(),
        ];

        foreach ($webhooks as $webhook) {
            $this->deliver($webhook, $event, $payload);
        }
    }

    public function sendTestPing(IntegrationWebhook $webhook): IntegrationWebhookDelivery
    {
        $payload = [
            'event' => 'webhook.test',
            'message' => 'Creator Operator test ping',
            'occurred_at' => now()->toIso8601String(),
        ];

        return $this->deliver($webhook, IntegrationEvent::PublishLogApproved, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function deliver(IntegrationWebhook $webhook, IntegrationEvent $event, array $payload): IntegrationWebhookDelivery
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($webhook->secret) {
            $headers['X-Creator-Operator-Signature'] = hash_hmac('sha256', json_encode($payload), $webhook->secret);
        }

        try {
            $response = Http::timeout(5)->withHeaders($headers)->post($webhook->url, $payload);
            $status = $response->status();
            $body = $response->body();
        } catch (\Throwable $e) {
            $status = 0;
            $body = $e->getMessage();
        }

        return IntegrationWebhookDelivery::query()->create([
            'integration_webhook_id' => $webhook->id,
            'event' => $payload['event'] ?? $event->value,
            'payload' => $payload,
            'response_status' => $status,
            'response_body' => substr((string) $body, 0, 2000),
            'delivered_at' => now(),
        ]);
    }

    protected function hookListens(IntegrationWebhook $hook, IntegrationEvent $event): bool
    {
        return match ($event) {
            IntegrationEvent::PublishLogApproved => $hook->on_approved,
            IntegrationEvent::PublishLogPublished => $hook->on_published,
        };
    }
}
