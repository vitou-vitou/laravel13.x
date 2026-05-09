<?php

namespace App\Filament\Resources\DemoItems\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DemoItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255),
            Textarea::make('body')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }
}
