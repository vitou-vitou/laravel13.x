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

    public function test_login_submit_button_is_disabled_until_form_is_filled(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('x-model="email"', false);
        $response->assertSee('x-model="password"', false);
        $response->assertSee('x-bind:disabled', false);
    }

    public function test_login_password_field_has_show_hide_toggle(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('showPassword', false);
        $response->assertSee('x-bind:type="showPassword ? \'text\' : \'password\'"', false);
        $response->assertSee('type="button"', false);
        $response->assertSee('x-bind:aria-label', false);
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
