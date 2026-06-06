<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Forms\TranslatableFields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            ...TranslatableFields::text('name', 'Name'),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
        ]);
    }
}
