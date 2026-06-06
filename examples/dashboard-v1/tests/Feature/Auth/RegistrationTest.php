<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee(__('Sign up'), false);
        $response->assertSee('Already have an account?', false);
        $response->assertSee(route('login'), false);
        $response->assertSee(__('Sign in'), false);
        $response->assertDontSee('<x-text-input', false);
        $response->assertSee('id="name"', false);
        $response->assertSee('id="email"', false);
        $response->assertSee('<input', false);
    }

    public function test_register_form_prevents_duplicate_submissions(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('submitting: false', false);
        $response->assertSee('x-on:submit="submitting = true"', false);
        $response->assertSee('x-bind:disabled', false);
        $response->assertSee('x-bind:aria-busy="submitting"', false);
        $response->assertSee(__('Signing up…'), false);
        $response->assertSee("'pointer-events-none': submitting", false);
        $response->assertSee('! name.trim() || ! email.trim() || ! password.trim() || ! password_confirmation.trim()', false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
