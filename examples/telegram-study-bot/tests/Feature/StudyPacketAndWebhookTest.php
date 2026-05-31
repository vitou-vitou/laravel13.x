<?php

namespace Tests\Feature;

use App\Services\Telegram\StudyPacketService;
use Tests\TestCase;

class StudyPacketAndWebhookTest extends TestCase
{
    public function test_bundled_study_packet_exists(): void
    {
        $path = app(StudyPacketService::class)->absolutePath();

        $this->assertFileExists($path);
        $this->assertStringEndsWith('.md', $path);
        $this->assertGreaterThan(10_000, filesize($path));
    }

    public function test_health_endpoint_reports_service_status(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertJsonPath('service', 'telegram-study-bot')
            ->assertJsonPath('study_packet_exists', true);
    }

    public function test_webhook_rejects_invalid_secret(): void
    {
        config(['telegram.webhook_secret' => 'expected-secret']);

        $response = $this->postJson('/telegram/webhook', [
            'message' => [
                'chat' => ['id' => 1],
                'text' => '/start',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'wrong',
        ]);

        $response->assertForbidden();
    }
}
