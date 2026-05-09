<?php

namespace App\Filament\Auth;

use App\Models\Guest;
use Filament\Actions\Action;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GuestLogin extends Login
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('How this demo works')
                    ->schema([
                        Text::make('• Guest mode: anonymous demo session on this browser.'),
                        Text::make('• No account recovery — clearing cookies loses access to this guest row.'),
                        Text::make('• Registration is not implemented in v1; a real app would link guests to users for cross-device data.'),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('continueAsGuest')
                ->label('Continue as guest')
                ->action('continueAsGuest')
                ->color('primary'),
        ];
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function continueAsGuest(): void
    {
        try {
            $guest = Guest::query()->create([
                'uuid' => (string) Str::uuid(),
            ]);
        } catch (\Throwable) {
            Notification::make()
                ->title('Could not start guest session')
                ->danger()
                ->send();

            return;
        }

        Filament::auth()->login($guest);
        session()->regenerate();

        Notification::make()
            ->title('Guest mode (demo)')
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }
}
