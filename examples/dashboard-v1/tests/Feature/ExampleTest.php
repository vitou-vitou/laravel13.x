<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_hello_on_home(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('hello');
    }
}
