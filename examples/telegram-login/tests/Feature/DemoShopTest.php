<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\TelegramBot;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_shop_page_loads(): void
    {
        $this->get(route('demo-shop.index'))->assertOk()->assertSee('Demo Shop');
    }

    public function test_demo_shop_login_redirects_when_configured(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Shop', 'slug' => 'shop', 'plan' => 'free']);

        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Shop App',
            'redirect_uris' => [route('demo-shop.callback')],
        ]);

        TelegramBot::query()->create([
            'application_id' => $application->id,
            'bot_username' => 'shop_bot',
            'bot_token' => '123:token',
        ]);

        $response = $this->get(route('demo-shop.login'));

        $response->assertRedirect();
        $this->assertStringContainsString('/auth/start', $response->headers->get('Location'));
    }
}
