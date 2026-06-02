<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KindlyLoginBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_kindly_login_branding(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kindly Login', false);
        $response->assertSee('logged in to Kindly Login', false);
    }
}
