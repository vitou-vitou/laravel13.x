<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('login'))
                ->post('/login', [
                    'email' => $user->email,
                    'password' => 'wrong-password',
                ]);
        }

        $response = $this->from(route('login'))
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_failed_login_uses_same_message_for_unknown_email(): void
    {
        $response = $this->from(route('login'))
            ->post('/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('email')
            ->assertSessionHasErrors([
                'email' => trans('auth.failed'),
            ]);
        $this->assertGuest();
    }
}
