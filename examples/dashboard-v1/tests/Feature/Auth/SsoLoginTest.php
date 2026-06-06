<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SsoLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_page_shows_google_button_when_configured(): void
    {
        $this->enableGoogleSso();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(config('app.name'), false)
            ->assertSee('Sign in with Google', false)
            ->assertSee('aria-label="Sign in with Google"', false)
            ->assertSee('min-h-[41px]', false)
            ->assertSee('#4285F4', false);
    }

    public function test_register_page_shows_google_button_when_configured(): void
    {
        $this->enableGoogleSso();

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Sign in with Google', false)
            ->assertSee('Sign-in options', false);
    }

    public function test_register_page_shows_microsoft_button_when_configured(): void
    {
        $this->enableMicrosoftSso();

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Sign in with Microsoft', false);
    }

    public function test_register_page_hides_sso_buttons_when_not_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.microsoft.client_id' => null,
            'services.microsoft.client_secret' => null,
        ]);

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('Sign in with Google', false)
            ->assertDontSee('Sign in with Microsoft', false);
    }

    public function test_register_page_autofocuses_name_when_sso_not_configured(): void
    {
        config(['services.google.client_id' => null, 'services.microsoft.client_id' => null]);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="name"', false)
            ->assertSee('autofocus', false);
    }

    public function test_register_page_skips_name_autofocus_when_sso_configured(): void
    {
        $this->enableGoogleSso();

        $html = $this->get(route('register'))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/id="name"[^>]*autofocus/', $html);
        $this->assertMatchesRegularExpression('/id="email"[^>]*autofocus/', $html);
    }

    public function test_sso_buttons_prevent_duplicate_navigation(): void
    {
        $this->enableBothSsoProviders();

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('navigating: false', false);
        $response->assertSee('if (navigating) { $event.preventDefault(); return; } navigating = true', false);
        $response->assertSee("'pointer-events-none opacity-50 cursor-wait': navigating", false);
    }

    public function test_login_page_shows_microsoft_button_when_configured(): void
    {
        $this->enableMicrosoftSso();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in with Microsoft', false)
            ->assertSee('aria-label="Sign in with Microsoft"', false)
            ->assertSee('Sign-in options', false)
            ->assertSee('#F25022', false);
    }

    public function test_login_page_skips_email_autofocus_when_sso_configured(): void
    {
        $this->enableGoogleSso();

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('autofocus', false);
    }

    public function test_login_page_autofocuses_email_when_sso_not_configured(): void
    {
        config(['services.google.client_id' => null, 'services.microsoft.client_id' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('autofocus', false);
    }

    public function test_login_page_hides_google_button_when_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Sign in with Google', false);
    }

    public function test_login_page_hides_microsoft_button_when_not_configured(): void
    {
        config(['services.microsoft.client_id' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Sign in with Microsoft', false);
    }

    public function test_login_page_shows_microsoft_before_google_when_both_configured(): void
    {
        $this->enableBothSsoProviders();

        $html = $this->get(route('login'))->assertOk()->getContent();
        $microsoftPos = strpos($html, 'Sign in with Microsoft');
        $googlePos = strpos($html, 'Sign in with Google');

        $this->assertNotFalse($microsoftPos);
        $this->assertNotFalse($googlePos);
        $this->assertLessThan($googlePos, $microsoftPos);
    }

    public function test_sso_redirect_goes_to_google_provider(): void
    {
        $this->enableGoogleSso();
        Socialite::fake('google');

        $this->get(route('sso.redirect', ['provider' => 'google']))
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_sso_redirect_goes_to_microsoft_provider(): void
    {
        $this->enableMicrosoftSso();
        Socialite::fake('microsoft');

        $this->get(route('sso.redirect', ['provider' => 'microsoft']))
            ->assertRedirect('https://socialite.fake/microsoft/authorize');
    }

    public function test_sso_redirect_returns_not_found_when_provider_disabled(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('sso.redirect', ['provider' => 'google']))
            ->assertNotFound();
    }

    public function test_sso_redirect_returns_not_found_for_unknown_provider(): void
    {
        $this->enableGoogleSso();

        $this->get(route('sso.redirect', ['provider' => 'facebook']))
            ->assertNotFound();
    }

    public function test_sso_callback_creates_new_microsoft_user_with_customer_role(): void
    {
        $this->enableMicrosoftSso();
        Socialite::fake('microsoft', $this->socialiteUser([
            'id' => 'microsoft-new-1',
            'name' => 'New M365 User',
            'email' => 'new-m365@example.com',
        ]));

        $response = $this->get(route('sso.callback', ['provider' => 'microsoft']));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertSame('new-m365@example.com', $user->email);
        $this->assertSame('microsoft', $user->sso_provider);
        $this->assertSame('microsoft-new-1', $user->sso_id);
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_sso_callback_creates_new_user_with_customer_role(): void
    {
        $this->enableGoogleSso();
        Socialite::fake('google', $this->socialiteUser([
            'id' => 'google-new-1',
            'name' => 'New SSO User',
            'email' => 'new-sso@example.com',
        ]));

        $response = $this->get(route('sso.callback', ['provider' => 'google']));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertSame('new-sso@example.com', $user->email);
        $this->assertSame('google', $user->sso_provider);
        $this->assertSame('google-new-1', $user->sso_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_sso_callback_links_existing_user_by_email(): void
    {
        $this->enableGoogleSso();
        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'sso_provider' => null,
            'sso_id' => null,
        ]);
        $existing->assignRole('admin');

        Socialite::fake('google', $this->socialiteUser([
            'id' => 'google-link-1',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
        ]));

        $this->get(route('sso.callback', ['provider' => 'google']))
            ->assertRedirect(route('dashboard', absolute: false));

        $existing->refresh();
        $this->assertSame('google', $existing->sso_provider);
        $this->assertSame('google-link-1', $existing->sso_id);
        $this->assertTrue($existing->hasRole('admin'));
    }

    public function test_sso_callback_logs_in_returning_sso_user(): void
    {
        $this->enableGoogleSso();
        $user = User::factory()->create([
            'email' => 'returning@example.com',
            'sso_provider' => 'google',
            'sso_id' => 'google-return-1',
        ]);
        $user->assignRole('customer');

        Socialite::fake('google', $this->socialiteUser([
            'id' => 'google-return-1',
            'name' => 'Returning User',
            'email' => 'returning@example.com',
        ]));

        $this->get(route('sso.callback', ['provider' => 'google']))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_users_cannot_visit_sso_routes(): void
    {
        $this->enableGoogleSso();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('sso.redirect', ['provider' => 'google']))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * @param  array{id?: string, name?: string, email?: string}  $overrides
     */
    private function socialiteUser(array $overrides = []): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->id = $overrides['id'] ?? 'google-123';
        $user->name = $overrides['name'] ?? 'Jane SSO';
        $user->email = $overrides['email'] ?? 'jane@gmail.com';

        return $user;
    }

    private function enableGoogleSso(): void
    {
        config([
            'services.google' => [
                'client_id' => 'test-google-client-id',
                'client_secret' => 'test-google-client-secret',
                'redirect' => 'http://dashboard-v1.test/auth/google/callback',
            ],
        ]);
    }

    private function enableMicrosoftSso(): void
    {
        config([
            'services.microsoft' => [
                'client_id' => 'test-microsoft-client-id',
                'client_secret' => 'test-microsoft-client-secret',
                'redirect' => 'http://dashboard-v1.test/auth/microsoft/callback',
                'tenant' => 'common',
            ],
        ]);
    }

    private function enableBothSsoProviders(): void
    {
        $this->enableGoogleSso();
        $this->enableMicrosoftSso();
    }
}
