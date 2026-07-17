<?php

namespace App\Filament\Resources\Tunnels\Schemas;

use App\Models\Tunnel;
use App\Rules\ValidHerdHost;
use App\Rules\ValidNgrokDomain;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TunnelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::fields(includeTemplatePicker: false));
    }

    public static function configureForCreate(Schema $schema): Schema
    {
        return $schema
            ->components(self::fields(includeTemplatePicker: true));
    }

    /**
     * @return array<int, Select|TextInput>
     */
    private static function fields(bool $includeTemplatePicker): array
    {
        $components = [];

        if ($includeTemplatePicker) {
            $components[] = Select::make('template_tunnel_id')
                ->label('Start from profile')
                ->options(fn (): array => Tunnel::query()
                    ->orderByDesc('is_active')
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->default(fn (): ?int => Tunnel::defaultTemplate()?->id)
                ->searchable()
                ->live()
                ->afterStateUpdated(function (?int $state, Set $set): void {
                    if ($state === null) {
                        return;
                    }

                    $tunnel = Tunnel::query()->find($state);

                    if ($tunnel === null) {
                        return;
                    }

                    foreach ($tunnel->templateAttributes() as $field => $value) {
                        $set($field, $value);
                    }
                })
                ->dehydrated(false)
                ->helperText('Copies Domain and Herd host from a saved profile. Pick a new Name below.');
        }

        $components[] = TextInput::make('name')
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);

        $components[] = TextInput::make('domain')
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->placeholder('your-name.ngrok-free.dev')
            ->helperText('Public ngrok hostname only — no https:// prefix.')
            ->rules([new ValidNgrokDomain]);

        $components[] = TextInput::make('herd_host')
            ->required()
            ->maxLength(255)
            ->default(config('tunnel.default_herd_host'))
            ->helperText('Synced to ngrok-traffic-policy.yml Host header when you Activate.')
            ->rules([new ValidHerdHost]);

        return $components;
    }
}
