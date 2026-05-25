<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // register

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['access_token', 'token_type', 'user']);

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_mismatched_password(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_with_short_password(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // login

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ])->assertStatus(422);
    }

    // user

    public function test_user_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/auth/user')
            ->assertStatus(200)
            ->assertJsonPath('id', $user->id);
    }

    public function test_user_endpoint_requires_auth(): void
    {
        $this->getJson('/api/auth/user')->assertStatus(401);
    }

    // logout

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/auth/logout')
            ->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully.');
    }

    public function test_logout_requires_auth(): void
    {
        $this->postJson('/api/auth/logout')->assertStatus(401);
    }
}
