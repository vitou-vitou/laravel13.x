<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_expected_columns(): void
    {
        $columns = \Schema::getColumnListing('users');

        $this->assertContains('username', $columns);
        $this->assertContains('country', $columns);
        $this->assertContains('city', $columns);
        $this->assertContains('device_type', $columns);
        $this->assertContains('signup_source', $columns);
        $this->assertContains('avatar', $columns);
        $this->assertContains('last_login_at', $columns);
    }

    public function test_seeder_creates_200_users(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->assertDatabaseCount('users', 200);
    }

    public function test_index_returns_200(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_keyword_filter_narrows_results(): void
    {
        \App\Models\User::factory()->create(['username' => 'zephyr_unique_xyz', 'email' => 'zephyr@example.com', 'country' => 'US', 'city' => 'New York']);
        \App\Models\User::factory()->count(10)->create();

        $response = $this->get('/?keyword=zephyr_unique_xyz');
        $response->assertStatus(200);
        $response->assertSee('zephyr_unique_xyz');
    }

    public function test_country_filter_narrows_results(): void
    {
        \App\Models\User::factory()->create(['username' => 'user_kh_only', 'email' => 'kh@example.com', 'country' => 'KH', 'city' => 'Phnom Penh']);
        \App\Models\User::factory()->count(5)->create(['country' => 'US']);

        $response = $this->get('/?country=KH');
        $response->assertStatus(200);
        $response->assertSee('user_kh_only');
    }

    public function test_pagination_works(): void
    {
        \App\Models\User::factory()->count(25)->create();

        $response = $this->get('/');
        $response->assertStatus(200);

        $response2 = $this->get('/?page=2');
        $response2->assertStatus(200);
    }
}
