<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\TelegramBot;
use App\Models\Tenant;
use App\Services\Telegram\OidcClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_start_rejects_invalid_redirect_uri(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Acme', 'slug' => 'acme', 'plan' => 'free']);

        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'App',
            'redirect_uris' => ['https://allowed.test/callback'],
        ]);

        TelegramBot::query()->create([
            'application_id' => $application->id,
            'bot_username' => 'testbot',
            'bot_token' => '123:token',
        ]);

        $response = $this->get('/auth/start?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => 'https://evil.test/callback',
            'state' => 'abc123',
            'code_challenge' => OidcClient::generateCodeChallenge(OidcClient::generateCodeVerifier()),
        ]));

        $response->assertStatus(400);
    }

    public function test_auth_start_shows_login_page_for_widget_flow(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Acme', 'slug' => 'acme', 'plan' => 'free']);

        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'App',
            'redirect_uris' => ['https://allowed.test/callback'],
        ]);

        TelegramBot::query()->create([
            'application_id' => $application->id,
            'bot_username' => 'testbot',
            'bot_token' => '123:token',
        ]);

        $response = $this->get('/auth/start?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => 'https://allowed.test/callback',
            'state' => 'abc123',
            'code_challenge' => OidcClient::generateCodeChallenge(OidcClient::generateCodeVerifier()),
            'flow' => 'widget',
        ]));

        $response->assertOk();
        $response->assertSee('testbot');
    }

    public function test_tenant_registration_creates_account(): void
    {
        $response = $this->post('/register', [
            'company_name' => 'New Co',
            'name' => 'Jane',
            'email' => 'jane@newco.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.onboarding'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('tenants', ['name' => 'New Co']);
    }
}
