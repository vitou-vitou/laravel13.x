<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill($this->devLoginDefaults());
    }

    /**
     * @return array<string, string>
     */
    protected function devLoginDefaults(): array
    {
        if (! config('filament-admin.dev_login.enabled')) {
            return [];
        }

        return [
            'email' => (string) config('filament-admin.dev_login.email'),
            'password' => (string) config('filament-admin.dev_login.password'),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->autocomplete('username');
    }
}
