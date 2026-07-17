<?php

namespace Tests\Feature\Filament;

use App\Filament\Auth\Login;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_login_form_prefills_dev_credentials_when_enabled(): void
    {
        config([
            'filament-admin.dev_login.enabled' => true,
            'filament-admin.dev_login.email' => 'test@example.com',
            'filament-admin.dev_login.password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->assertFormSet([
                'email' => 'test@example.com',
                'password' => 'password',
            ]);
    }

    public function test_login_form_is_empty_when_dev_prefill_disabled(): void
    {
        config(['filament-admin.dev_login.enabled' => false]);

        Livewire::test(Login::class)
            ->assertFormSet([
                'email' => null,
                'password' => null,
            ]);
    }

    public function test_admin_login_page_is_reachable(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}
