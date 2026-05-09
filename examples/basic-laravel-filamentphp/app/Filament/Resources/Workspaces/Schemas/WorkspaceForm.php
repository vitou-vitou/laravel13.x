<?php

namespace App\Filament\Resources\Workspaces\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkspaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
