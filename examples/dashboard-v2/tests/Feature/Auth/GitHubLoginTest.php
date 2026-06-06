<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GitHubLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_github_button_when_configured(): void
    {
        $this->enableGitHub();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in with GitHub', false)
            ->assertSee('aria-label="Sign in with GitHub"', false);
    }

    public function test_login_page_hides_github_button_when_not_configured(): void
    {
        config(['services.github.client_id' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Sign in with GitHub', false);
    }

    public function test_login_page_skips_email_autofocus_when_github_configured(): void
    {
        $this->enableGitHub();

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('autofocus', false);
    }

    public function test_github_redirect_goes_to_github_provider(): void
    {
        $this->enableGitHub();
        Socialite::fake('github');

        $this->get(route('github.redirect'))
            ->assertRedirect('https://socialite.fake/github/authorize');
    }

    public function test_github_redirect_returns_not_found_when_disabled(): void
    {
        config(['services.github.client_id' => null]);

        $this->get(route('github.redirect'))
            ->assertNotFound();
    }

    public function test_github_callback_creates_new_user(): void
    {
        $this->enableGitHub();
        Socialite::fake('github', $this->socialiteUser([
            'id' => 'gh-new-1',
            'name' => 'New GitHub User',
            'email' => 'new-github@example.com',
            'avatar' => 'https://avatars.githubusercontent.com/u/1?v=4',
        ]));

        $response = $this->get(route('github.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertSame('new-github@example.com', $user->email);
        $this->assertSame('gh-new-1', $user->github_id);
        $this->assertSame('https://avatars.githubusercontent.com/u/1?v=4', $user->avatar);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_github_callback_links_existing_user_by_email(): void
    {
        $this->enableGitHub();
        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'github_id' => null,
            'avatar' => null,
        ]);

        Socialite::fake('github', $this->socialiteUser([
            'id' => 'gh-link-1',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'avatar' => 'https://avatars.githubusercontent.com/u/2?v=4',
        ]));

        $this->get(route('github.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $existing->refresh();
        $this->assertSame('gh-link-1', $existing->github_id);
        $this->assertSame('https://avatars.githubusercontent.com/u/2?v=4', $existing->avatar);
    }

    public function test_github_callback_logs_in_returning_github_user(): void
    {
        $this->enableGitHub();
        $user = User::factory()->create([
            'email' => 'returning@example.com',
            'github_id' => 'gh-return-1',
            'avatar' => 'https://avatars.githubusercontent.com/u/3?v=4',
        ]);

        Socialite::fake('github', $this->socialiteUser([
            'id' => 'gh-return-1',
            'name' => 'Returning User',
            'email' => 'returning@example.com',
        ]));

        $this->get(route('github.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_users_cannot_visit_github_routes(): void
    {
        $this->enableGitHub();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('github.redirect'))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_dashboard_shows_github_avatar_when_present(): void
    {
        $user = User::factory()->create([
            'github_id' => 'gh-dash-1',
            'avatar' => 'https://avatars.githubusercontent.com/u/99?v=4',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('https://avatars.githubusercontent.com/u/99?v=4', false)
            ->assertSee($user->name, false);
    }

    /**
     * @param  array{id?: string, name?: string, email?: string, avatar?: string}  $overrides
     */
    private function socialiteUser(array $overrides = []): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->id = $overrides['id'] ?? 'github-123';
        $user->name = $overrides['name'] ?? 'Jane GitHub';
        $user->email = $overrides['email'] ?? 'jane@users.noreply.github.com';
        $user->avatar = $overrides['avatar'] ?? 'https://avatars.githubusercontent.com/u/123?v=4';

        return $user;
    }

    private function enableGitHub(): void
    {
        config([
            'services.github' => [
                'client_id' => 'test-github-client-id',
                'client_secret' => 'test-github-client-secret',
                'redirect' => 'http://dashboard-v2.test/auth/github/callback',
            ],
        ]);
    }
}
