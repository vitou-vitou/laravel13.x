<?php

namespace App\Filament\Resources\Tunnels\Pages;

use App\Filament\Resources\Tunnels\Schemas\TunnelForm;
use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Tunnel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateTunnel extends CreateRecord
{
    protected static string $resource = TunnelResource::class;

    public function form(Schema $schema): Schema
    {
        return TunnelForm::configureForCreate($schema);
    }

    public function mount(): void
    {
        parent::mount();

        $template = Tunnel::defaultTemplate();

        if ($template === null) {
            return;
        }

        $this->form->fill([
            'template_tunnel_id' => $template->id,
            ...$template->templateAttributes(),
        ]);
    }
}
