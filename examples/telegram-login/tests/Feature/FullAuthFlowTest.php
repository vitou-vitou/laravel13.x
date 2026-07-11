<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AuthSession;
use App\Models\TelegramBot;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Telegram\OidcClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FullAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seedApplication(): array
    {
        $botToken = '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';
        $plainSecret = 'known-client-secret-for-tests';
        $codeVerifier = OidcClient::generateCodeVerifier();
        $codeChallenge = OidcClient::generateCodeChallenge($codeVerifier);

        $tenant = Tenant::query()->create(['name' => 'Acme', 'slug' => 'acme', 'plan' => 'free']);

        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Store App',
            'client_secret' => $plainSecret,
            'redirect_uris' => ['https://store.test/auth/callback'],
        ]);

        TelegramBot::query()->create([
            'application_id' => $application->id,
            'bot_username' => 'acme_bot',
            'bot_token' => $botToken,
        ]);

        return compact('botToken', 'plainSecret', 'codeVerifier', 'codeChallenge', 'application');
    }

    private function buildTelegramPayload(int $telegramId, string $botToken): array
    {
        $data = [
            'id' => (string) $telegramId,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'janedoe',
            'auth_date' => (string) time(),
        ];

        $dataCheckString = collect($data)->sortKeys()->map(fn ($v, $k) => $k.'='.$v)->implode("\n");
        $secretKey = hash('sha256', $botToken, true);
        $data['hash'] = hash_hmac('sha256', $dataCheckString, $secretKey);

        return $data;
    }

    public function test_full_widget_login_token_and_userinfo_flow(): void
    {
        [
            'botToken' => $botToken,
            'plainSecret' => $plainSecret,
            'codeVerifier' => $codeVerifier,
            'codeChallenge' => $codeChallenge,
            'application' => $application,
        ] = $this->seedApplication();

        $clientState = 'tenant-csrf-state-abc';

        $this->get('/auth/start?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => 'https://store.test/auth/callback',
            'state' => $clientState,
            'code_challenge' => $codeChallenge,
            'flow' => 'widget',
        ]))->assertOk()->assertSee('Completing Telegram login', false);

        $session = AuthSession::query()->where('application_id', $application->id)->first();
        $this->assertNotNull($session);
        $this->assertSame($clientState, $session->client_state);

        $telegramPayload = $this->buildTelegramPayload(987654321, $botToken);

        $callbackResponse = $this->get('/auth/callback/widget?'.http_build_query([
            ...$telegramPayload,
            'state' => $session->state,
        ]));

        $callbackResponse->assertRedirect();
        $location = $callbackResponse->headers->get('Location');
        $this->assertStringStartsWith('https://store.test/auth/callback', $location);

        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame($clientState, $query['state']);
        $this->assertNotEmpty($query['code']);

        $tokenResponse = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $query['code'],
            'redirect_uri' => 'https://store.test/auth/callback',
            'client_id' => $application->client_id,
            'client_secret' => $plainSecret,
            'code_verifier' => $codeVerifier,
        ]);

        $tokenResponse->assertOk();
        $tokenResponse->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);
        $tokenResponse->assertJsonPath('user.telegram_id', 987654321);
        $tokenResponse->assertJsonPath('user.username', 'janedoe');

        $accessToken = $tokenResponse->json('access_token');

        $this->getJson('/oauth/userinfo', [
            'Authorization' => 'Bearer '.$accessToken,
        ])
            ->assertOk()
            ->assertJsonPath('telegram_id', 987654321)
            ->assertJsonPath('first_name', 'Jane');
    }

    public function test_dashboard_onboarding_and_home_after_login(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Shop', 'slug' => 'shop', 'plan' => 'free']);

        User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => 'owner@shop.test',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => 'owner@shop.test',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.home'));

        $this->get('/dashboard/onboarding')->assertOk();

        $this->post('/dashboard/onboarding/application', [
            'name' => 'Shop App',
            'redirect_uri' => 'https://shop.test/callback',
        ])->assertRedirect(route('dashboard.onboarding'));

        $application = Application::query()->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($application);

        $this->post('/dashboard/onboarding/bot', [
            'application_id' => $application->id,
            'bot_username' => 'shop_bot',
            'bot_token' => '999:SHOP_BOT_TOKEN',
            'domain' => 'shop.test',
        ])->assertRedirect(route('dashboard.onboarding'));

        $this->get('/dashboard')->assertOk()->assertSee('Shop App')->assertSee('shop_bot');
    }

    public function test_auth_start_completes_login_when_telegram_returns_to_start_url(): void
    {
        [
            'botToken' => $botToken,
            'codeVerifier' => $codeVerifier,
            'codeChallenge' => $codeChallenge,
            'application' => $application,
        ] = $this->seedApplication();

        $clientState = 'return-to-start-state';

        $this->get('/auth/start?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => 'https://store.test/auth/callback',
            'state' => $clientState,
            'code_challenge' => $codeChallenge,
            'flow' => 'widget',
        ]))->assertOk()->assertSee('Completing Telegram login', false);

        $session = AuthSession::query()->where('client_state', $clientState)->firstOrFail();
        $payload = $this->buildTelegramPayload(424242, $botToken);

        $response = $this->get('/auth/start?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => 'https://store.test/auth/callback',
            'state' => $clientState,
            'code_challenge' => $codeChallenge,
            'flow' => 'widget',
            ...$payload,
        ]));

        $response->assertRedirect();
        $this->assertStringStartsWith('https://store.test/auth/callback', $response->headers->get('Location'));
    }

    public function test_token_exchange_rejects_wrong_client_secret(): void
    {
        [
            'botToken' => $botToken,
            'codeVerifier' => $codeVerifier,
            'codeChallenge' => $codeChallenge,
            'application' => $application,
        ] = $this->seedApplication();

        $this->get('/auth/start?'.http_build_query([
            'client_id' => $application->client_id,
            'redirect_uri' => 'https://store.test/auth/callback',
            'state' => 'state1',
            'code_challenge' => $codeChallenge,
        ]));

        $session = AuthSession::query()->firstOrFail();
        $payload = $this->buildTelegramPayload(111, $botToken);

        $location = $this->get('/auth/callback/widget?'.http_build_query([
            ...$payload,
            'state' => $session->state,
        ]))->headers->get('Location');

        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $query['code'],
            'redirect_uri' => 'https://store.test/auth/callback',
            'client_id' => $application->client_id,
            'client_secret' => 'wrong-secret',
            'code_verifier' => $codeVerifier,
        ])->assertStatus(401)->assertJson(['error' => 'invalid_client']);
    }
}
