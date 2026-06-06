<?php

namespace App\Filament\Resources\Tunnels\Schemas;

use App\Models\Tunnel;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TunnelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('domain')
                    ->copyable(),
                TextEntry::make('herd_host')
                    ->label('Herd host'),
                IconEntry::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextEntry::make('last_verified_status')
                    ->label('Last health check')
                    ->placeholder('—'),
                TextEntry::make('last_verified_at')
                    ->dateTime()
                    ->placeholder('—'),
                TextEntry::make('oauth_urls')
                    ->label('SSO login')
                    ->state(fn (Tunnel $record): string => $record->oauthUrls()['login'] ?? '')
                    ->copyable()
                    ->columnSpanFull(),
                TextEntry::make('google_callback')
                    ->label('Google redirect URI')
                    ->state(fn (Tunnel $record): string => $record->oauthUrls()['GOOGLE_REDIRECT_URI'] ?? '')
                    ->copyable()
                    ->columnSpanFull(),
                TextEntry::make('microsoft_callback')
                    ->label('Microsoft redirect URI')
                    ->state(fn (Tunnel $record): string => $record->oauthUrls()['MICROSOFT_REDIRECT_URI'] ?? '')
                    ->copyable()
                    ->columnSpanFull(),
                TextEntry::make('github_callback')
                    ->label('GitHub callback URL')
                    ->state(fn (Tunnel $record): string => $record->oauthUrls()['GITHUB_REDIRECT_URI'] ?? '')
                    ->copyable()
                    ->columnSpanFull(),
            ]);
    }
}
