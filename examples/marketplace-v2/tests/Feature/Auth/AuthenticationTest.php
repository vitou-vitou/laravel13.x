<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_login_form_prefills_dev_credentials_when_enabled(): void
    {
        config([
            'dev-login.enabled' => true,
            'dev-login.email' => 'admin@marketplace.local',
            'dev-login.password' => 'password',
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Development login', false);
        $response->assertSee('value="admin@marketplace.local"', false);
        $response->assertSee('value="password"', false);
    }

    public function test_login_form_is_empty_when_dev_prefill_disabled(): void
    {
        config(['dev-login.enabled' => false]);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('Development login', false);
        $response->assertDontSee('value="admin@marketplace.local"', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
