<?php

namespace App\Filament\Resources\Tunnels\Tables;

use App\Models\Tunnel;
use App\Services\Tunnel\TunnelActivator;
use App\Services\Tunnel\TunnelHealthChecker;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TunnelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domain')
                    ->searchable()
                    ->copyable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('last_verified_status')
                    ->label('Health')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'ok' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                TextColumn::make('last_verified_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('activate')
                    ->label('Activate')
                    ->icon(Heroicon::OutlinedBolt)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Sync NGROK_DEV_DOMAIN, OAuth redirect URIs in .env, and Herd Host in ngrok-traffic-policy.yml.')
                    ->visible(fn (Tunnel $record): bool => ! $record->is_active)
                    ->action(function (Tunnel $record, TunnelActivator $activator): void {
                        $result = $activator->activate($record);

                        $body = collect($result['oauth_urls'])
                            ->map(fn (string $url, string $key): string => "{$key}: {$url}")
                            ->implode("\n");

                        Notification::make()
                            ->title('Tunnel activated')
                            ->body("Base URL: {$result['base_url']}\n\n{$body}")
                            ->success()
                            ->send();
                    }),
                Action::make('verify')
                    ->label('Verify')
                    ->icon(Heroicon::OutlinedSignal)
                    ->action(function (Tunnel $record, TunnelHealthChecker $checker): void {
                        $result = $checker->verifyAndStore($record);

                        Notification::make()
                            ->title($result['status'] === 'ok' ? 'Tunnel healthy' : 'Tunnel check failed')
                            ->body($result['message'])
                            ->{$result['status'] === 'ok' ? 'success' : 'danger'}()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
