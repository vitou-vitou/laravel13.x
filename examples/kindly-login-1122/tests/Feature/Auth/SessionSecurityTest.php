<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_regenerates_session_id(): void
    {
        $user = User::factory()->create();

        $this->get(route('login'));

        $sessionBeforeLogin = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertNotSame($sessionBeforeLogin, session()->getId());
    }

    public function test_logout_invalidates_protected_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/logout')->assertRedirect('/');

        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
}
