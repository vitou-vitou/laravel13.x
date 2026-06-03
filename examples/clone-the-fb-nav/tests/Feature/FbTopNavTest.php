<?php

namespace Tests\Feature;

use Tests\TestCase;

class FbTopNavTest extends TestCase
{
    public function test_home_page_renders_fb_top_nav(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('role="banner"', false);
        $response->assertSee('aria-label="Facebook"', false);
        $response->assertSee('>Home<', false);
        $response->assertSee('>Watch<', false);
        $response->assertSee('>Marketplace<', false);
        $response->assertSee('>Groups<', false);
        $response->assertSee('>Gaming<', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('href="'.url('/watch').'"', false);
    }

    public function test_watch_route_marks_watch_active(): void
    {
        $response = $this->get('/watch');

        $response->assertOk();
        $response->assertSee('data-nav-tab="watch"', false);
        $response->assertSee('data-active="true"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertDontSee('data-nav-tab="home" data-active="true"', false);
    }

    public function test_marketplace_route_marks_marketplace_active(): void
    {
        $response = $this->get('/marketplace');

        $response->assertOk();
        $response->assertSee('data-nav-tab="marketplace"', false);
        $response->assertSee('data-active="true"', false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_utility_controls_have_accessible_names(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('aria-label="Search Facebook"', false);
        $response->assertSee('aria-label="Menu"', false);
        $response->assertSee('aria-label="Messenger"', false);
        $response->assertSee('aria-label="Notifications"', false);
        $response->assertSee('aria-label="Account menu"', false);
    }
}
