<?php

namespace App\Filament\Resources\Tunnels;

use App\Filament\Resources\Tunnels\Pages\CreateTunnel;
use App\Filament\Resources\Tunnels\Pages\EditTunnel;
use App\Filament\Resources\Tunnels\Pages\ListTunnels;
use App\Filament\Resources\Tunnels\Pages\ViewTunnel;
use App\Filament\Resources\Tunnels\Schemas\TunnelForm;
use App\Filament\Resources\Tunnels\Schemas\TunnelInfolist;
use App\Filament\Resources\Tunnels\Tables\TunnelsTable;
use App\Models\Tunnel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TunnelResource extends Resource
{
    protected static ?string $model = Tunnel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Tunnels';

    protected static string|\UnitEnum|null $navigationGroup = 'Development';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'tunnel';

    protected static ?string $pluralModelLabel = 'tunnels';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::tunnelAdminEnabled();
    }

    public static function canCreate(): bool
    {
        return static::tunnelAdminEnabled();
    }

    public static function canEdit($record): bool
    {
        return static::tunnelAdminEnabled();
    }

    public static function canDelete($record): bool
    {
        if (! static::tunnelAdminEnabled()) {
            return false;
        }

        return $record instanceof Tunnel && ! $record->is_active;
    }

    public static function canView($record): bool
    {
        return static::tunnelAdminEnabled();
    }

    protected static function tunnelAdminEnabled(): bool
    {
        if (! config('tunnel.enabled')) {
            return false;
        }

        $user = Auth::user();

        return $user !== null && $user->can('manage_dev_tunnels');
    }

    public static function form(Schema $schema): Schema
    {
        return TunnelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TunnelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TunnelsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTunnels::route('/'),
            'create' => CreateTunnel::route('/create'),
            'view' => ViewTunnel::route('/{record}'),
            'edit' => EditTunnel::route('/{record}/edit'),
        ];
    }
}
