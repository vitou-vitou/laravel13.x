<?php

namespace Tests\Feature;

use App\Services\LoginLimiter;
use Tests\TestCase;

class DashboardLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app(LoginLimiter::class)->reset('127.0.0.1');
        app(LoginLimiter::class)->reset('192.168.1.50');
    }

    public function test_login_page_renders_successfully(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('9Router')
            ->assertSee('Default password is');
    }

    public function test_successful_login_with_default_password(): void
    {
        $this->postJson('/api/auth/login', [
            'password' => '123456',
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'mustChangePassword' => false,
            ]);

        $this->assertAuthenticatedOnDashboard();
    }

    public function test_invalid_password_returns_401_with_remaining_attempts(): void
    {
        $this->postJson('/api/auth/login', [
            'password' => 'wrong',
        ])
            ->assertStatus(401)
            ->assertJson([
                'remainingBeforeLock' => 4,
            ]);
    }

    public function test_progressive_lockout_after_five_failed_attempts(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/auth/login', ['password' => 'wrong'])->assertStatus(401);
        }

        $this->postJson('/api/auth/login', ['password' => 'wrong'])
            ->assertStatus(429)
            ->assertHeader('Retry-After', '30')
            ->assertJsonStructure(['error', 'retryAfter', 'resetHint']);
    }

    public function test_remote_default_password_blocked_with_403(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->postJson('/api/auth/login', [
                'password' => '123456',
            ])
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'mustChangePassword' => true,
            ]);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_accesses_dashboard(): void
    {
        $this->withSession(['dashboard_authenticated' => true])
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome to 9Router Clone');
    }

    public function test_status_endpoint_reports_auth_state(): void
    {
        $this->getJson('/api/auth/status')
            ->assertOk()
            ->assertJson([
                'requireLogin' => true,
                'authMode' => 'password',
                'authenticated' => false,
            ]);

        $this->withSession(['dashboard_authenticated' => true])
            ->getJson('/api/auth/status')
            ->assertOk()
            ->assertJson([
                'authenticated' => true,
            ]);
    }

    public function test_logout_invalidates_session(): void
    {
        $this->withSession(['dashboard_authenticated' => true])
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertGuestOnDashboard();
    }

    private function assertAuthenticatedOnDashboard(): void
    {
        $this->assertTrue(session('dashboard_authenticated') === true);
    }

    private function assertGuestOnDashboard(): void
    {
        $this->assertTrue(session('dashboard_authenticated') !== true);
    }
}
