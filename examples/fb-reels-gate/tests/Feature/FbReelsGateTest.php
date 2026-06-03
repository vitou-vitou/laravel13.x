<?php

namespace Tests\Feature;

use Tests\TestCase;

class FbReelsGateTest extends TestCase
{
    public function test_reel_route_renders_login_gate(): void
    {
        $response = $this->get('/reel/879785385158813');

        $response->assertOk();
        $response->assertSee('data-reels-gate="true"', false);
        $response->assertSee('>Reels<', false);
        $response->assertSee('See more on Facebook', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
    }

    public function test_login_modal_has_accessible_form_controls(): void
    {
        $response = $this->get('/reel/879785385158813');

        $response->assertOk();
        $response->assertSee('aria-label="Close login dialog"', false);
        $response->assertSee('id="reels-gate-email"', false);
        $response->assertSee('id="reels-gate-password"', false);
        $response->assertSee('Log in to Facebook', false);
        $response->assertSee('Forgot password?', false);
        $response->assertSee('Create new account', false);
    }

    public function test_header_has_public_facebook_branding(): void
    {
        $response = $this->get('/reel/879785385158813');

        $response->assertOk();
        $response->assertSee('aria-label="Facebook"', false);
        $response->assertSee('>facebook<', false);
    }
}
