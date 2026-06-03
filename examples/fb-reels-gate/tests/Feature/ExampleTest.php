<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_home_renders_login_gate(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-reels-gate="true"', false);
    }
}
