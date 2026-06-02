<?php

namespace Tests\Feature;

use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KindlyEcommerceBrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_shop_home_shows_kindly_ecommerce_branding(): void
    {
        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertSee('Kindly E-Commerce', false);
        $response->assertSee('Browse our catalog', false);
    }
}
