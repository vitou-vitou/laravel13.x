<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{DatePicker, DateTimePicker, Select, Textarea, TextInput, Toggle};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\SelectFilter;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'Projects';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('key')->required()->maxLength(255),
            TextInput::make('icon')->required()->maxLength(255),
            Select::make('type')->options([
                    'scrum' => 'Scrum',
                    'kanban' => 'Kanban',
                ])->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('key')->searchable()->sortable(),
            TextColumn::make('icon')->searchable()->sortable(),
            TextColumn::make('type')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('type')->options([
                    'scrum' => 'Scrum',
                    'kanban' => 'Kanban',
                ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            SprintRelationManager::class,
            IssueRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view'   => Pages\ViewProject::route('/{record}'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}