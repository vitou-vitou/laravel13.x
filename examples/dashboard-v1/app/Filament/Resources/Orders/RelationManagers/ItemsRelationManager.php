<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product'),
                TextColumn::make('quantity'),
                TextColumn::make('unit_price_cents')
                    ->label('Unit price')
                    ->formatStateUsing(fn (int $state): string => '$'.number_format($state / 100, 2)),
                TextColumn::make('line_total_cents')
                    ->label('Line total')
                    ->formatStateUsing(fn (int $state): string => '$'.number_format($state / 100, 2)),
            ])
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
