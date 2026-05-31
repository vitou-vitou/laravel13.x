<?php

namespace Tests\Unit;

use App\Services\Telegram\UpdateRouter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UpdateRouterTest extends TestCase
{
    public function test_start_command_routes_to_welcome(): void
    {
        Config::set('telegram.allowed_chat_ids', []);

        $router = new UpdateRouter;

        $route = $router->route([
            'message' => [
                'chat' => ['id' => 12345],
                'text' => '/start',
            ],
        ]);

        $this->assertSame(UpdateRouter::ACTION_WELCOME, $route['action']);
        $this->assertSame(12345, $route['chat_id']);
    }

    public function test_study_command_routes_to_send_packet(): void
    {
        Config::set('telegram.allowed_chat_ids', []);

        $router = new UpdateRouter;

        $route = $router->route([
            'message' => [
                'chat' => ['id' => 99],
                'text' => '/study',
            ],
        ]);

        $this->assertSame(UpdateRouter::ACTION_SEND_PACKET, $route['action']);
    }

    public function test_denied_when_chat_not_in_allow_list(): void
    {
        Config::set('telegram.allowed_chat_ids', ['111']);

        $router = new UpdateRouter;

        $route = $router->route([
            'message' => [
                'chat' => ['id' => 222],
                'text' => '/study',
            ],
        ]);

        $this->assertSame(UpdateRouter::ACTION_DENIED, $route['action']);
    }
}
