<?php

namespace App\Filament\Resources\Tunnels\Pages;

use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Tunnel;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTunnel extends EditRecord
{
    protected static string $resource = TunnelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (Tunnel $record): bool => TunnelResource::canDelete($record)),
        ];
    }
}
