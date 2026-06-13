<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_healthz_returns_ok_with_database(): void
    {
        $response = $this->getJson('/api/healthz');

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'database' => 'ok',
            ]);
    }
}
