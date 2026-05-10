<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_oauth_desktop_success_renders(): void
    {
        $response = $this->get('/oauth/desktop/success');

        $response->assertStatus(200);
        $response->assertSee('Successfully logged in', false);
    }

    public function test_root_redirects_to_demo(): void
    {
        $this->get('/')->assertRedirect('/oauth/desktop/success');
    }
}
