<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_api(): void
    {
        $this->getJson('/api/my/bookings')->assertUnauthorized();
    }

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Customer->value,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['data' => ['token', 'user' => ['id', 'email']]]);
        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    }

    public function test_user_can_login_with_token_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk();
        $token = $login->json('data.token');

        $this->withToken($token)
            ->getJson('/api/my/bookings')
            ->assertOk();

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertSame(0, $user->fresh()->tokens()->count());
    }

    public function test_invalid_token_is_unauthorized(): void
    {
        $this->withToken('invalid-token')
            ->getJson('/api/my/bookings')
            ->assertUnauthorized();
    }
}
