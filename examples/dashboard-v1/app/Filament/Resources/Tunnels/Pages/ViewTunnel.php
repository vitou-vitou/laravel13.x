<?php

namespace App\Filament\Resources\Tunnels\Pages;

use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Tunnel;
use App\Services\Tunnel\TunnelActivator;
use App\Services\Tunnel\TunnelHealthChecker;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewTunnel extends ViewRecord
{
    protected static string $resource = TunnelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label('Activate')
                ->icon(Heroicon::OutlinedBolt)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Tunnel $record): bool => ! $record->is_active)
                ->action(function (Tunnel $record, TunnelActivator $activator): void {
                    $activator->activate($record);

                    Notification::make()
                        ->title('Tunnel activated')
                        ->body('OAuth redirect URIs synced to .env and Herd host updated in ngrok-traffic-policy.yml.')
                        ->success()
                        ->send();
                }),
            Action::make('verify')
                ->label('Verify health')
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
        ];
    }
}
