<?php

namespace Tests\Unit;

use App\Services\Telegram\TelegramOAuthClient;
use Tests\TestCase;

class TelegramOAuthClientTest extends TestCase
{
    public function test_builds_full_page_oauth_url_without_embed(): void
    {
        config(['app.url' => 'https://laravel13.x.test']);

        $client = new TelegramOAuthClient;
        $url = $client->buildAuthorizationUrl(
            new \App\Models\TelegramBot(['bot_token' => '8654173470:ABC-DEF']),
            'https://laravel13.x.test/auth/start?client_id=test'
        );

        $this->assertStringStartsWith('https://oauth.telegram.org/auth?', $url);
        $this->assertStringContainsString('bot_id=8654173470', $url);
        $this->assertStringContainsString('origin=https%3A%2F%2Flaravel13.x.test', $url);
        $this->assertStringNotContainsString('embed=1', $url);
    }

    public function test_uses_bot_registered_domain_for_origin(): void
    {
        config(['app.url' => 'https://laravel13.x.test']);

        $client = new TelegramOAuthClient;
        $url = $client->buildAuthorizationUrl(
            new \App\Models\TelegramBot([
                'bot_token' => '8654173470:ABC-DEF',
                'domains' => ['laravel13.x.test'],
            ]),
            'https://laravel13.x.test/auth/start?client_id=test'
        );

        $this->assertStringContainsString('origin=https%3A%2F%2Flaravel13.x.test', $url);
    }
}
