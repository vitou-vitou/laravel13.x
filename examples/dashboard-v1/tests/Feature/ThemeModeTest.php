<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_theme_toggle(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Theme"', false)
            ->assertSee(__('Light'), false)
            ->assertSee(__('Dark'), false)
            ->assertSee(__('Auto'), false);
    }

    public function test_login_includes_theme_toggle(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('aria-label="Theme"', false)
            ->assertSee(__('Light'), false)
            ->assertSee(__('Dark'), false)
            ->assertSee(__('Auto'), false);
    }

    public function test_forgot_password_includes_theme_toggle(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('aria-label="Theme"', false)
            ->assertSee(__('Light'), false)
            ->assertSee(__('Dark'), false)
            ->assertSee(__('Auto'), false);
    }

    public function test_register_includes_theme_toggle(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('aria-label="Theme"', false)
            ->assertSee(__('Light'), false)
            ->assertSee(__('Dark'), false)
            ->assertSee(__('Auto'), false);
    }

    public function test_layout_includes_theme_init_script(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee("localStorage.getItem('theme')", false)
            ->assertSee('prefers-color-scheme', false);
    }
}
