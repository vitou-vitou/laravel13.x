<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Forms\TranslatableFields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')
                ->relationship('category', 'slug')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                ->searchable()
                ->preload()
                ->required(),
            ...TranslatableFields::text('name', 'Name'),
            ...TranslatableFields::text('description', 'Description', textarea: true),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('price_cents')
                ->label('Price (cents)')
                ->numeric()
                ->required()
                ->minValue(1),
            Toggle::make('is_active')
                ->default(true),
        ]);
    }
}
