<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleAndPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_home_ok(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(__('home.title'), false);
    }

    public function test_services_page_ok(): void
    {
        $response = $this->get('/services/laravel');

        $response->assertOk();
        $response->assertSee(__('services.title'), false);
    }

    public function test_locale_switch_sets_session_and_changes_copy(): void
    {
        $this->from('/')->get('/locale/zh_CN')->assertRedirect();

        $this->assertSame('zh_CN', session('locale'));

        app()->setLocale('zh_CN');
        $response = $this->withSession(['locale' => 'zh_CN'])->get('/');
        $response->assertOk();
        $response->assertSee(__('home.title'), false);
    }

    public function test_privacy_ok(): void
    {
        $this->get('/privacy')->assertOk();
    }

    public function test_cookie_consent_sets_cookie(): void
    {
        $response = $this->from('/')->post('/cookie-consent');

        $response->assertRedirect('/');
        $response->assertCookie('cookie_consent', '1');
    }
}
