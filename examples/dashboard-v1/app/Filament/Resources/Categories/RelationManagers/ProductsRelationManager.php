<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Forms\TranslatableFields;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ...TranslatableFields::text('product_name', 'Name'),
            ...TranslatableFields::text('product_description', 'Description', textarea: true),
            TextInput::make('slug')
                ->required()
                ->maxLength(255),
            TextInput::make('price_cents')
                ->label('Price (cents)')
                ->numeric()
                ->required()
                ->minValue(1),
            Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(fn ($state, $record) => $record->getTranslation('name', 'en'))
                    ->searchable(),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state): string => '$'.number_format($state / 100, 2))
                    ->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => self::mapProductData($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => self::mapProductData($data)),
                DeleteAction::make(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function mapProductData(array $data): array
    {
        $data = TranslatableFields::collapse($data, ['product_name', 'product_description']);
        $data['name'] = $data['product_name'] ?? [];
        $data['description'] = $data['product_description'] ?? [];
        unset($data['product_name'], $data['product_description']);

        return $data;
    }
}
