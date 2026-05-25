<?php
namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class IssueRelationManager extends RelationManager
{
    protected static string $relationship = 'issues';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Forms\Components\TextInput::make('name')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}