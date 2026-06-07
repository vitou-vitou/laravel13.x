<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomeTabsTest extends TestCase
{
    public function test_welcome_page_shows_two_tabs_with_lorem_content(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-tab-persist="localStorage"', false);
        $response->assertSee('tab1', false);
        $response->assertSee('tab2', false);
        $response->assertSee('supabase', false);
        $response->assertSee('logs', false);
        $response->assertSee('data-supabase-sub="health"', false);
        $response->assertSee('>Health<', false);
        $response->assertSee('data-supabase-status="idle"', false);
        $response->assertSee('Activity logs', false);
        $response->assertSee('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', false);
        $response->assertSee('Sed ut perspiciatis unde omnis iste natus error sit voluptatem.', false);
        $response->assertDontSee("Let's get started", false);
    }
}
