<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', 'en'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->formatStateUsing(fn ($state, $record) => $record->category?->getTranslation('name', 'en'))
                    ->label('Category'),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state): string => '$'.number_format($state / 100, 2))
                    ->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
