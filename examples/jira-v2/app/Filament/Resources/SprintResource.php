<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SprintResource\Pages;
use App\Filament\Resources\SprintResource\RelationManagers;
use App\Models\Sprint;
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

class SprintResource extends Resource
{
    protected static ?string $model = Sprint::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static string|\UnitEnum|null $navigationGroup = 'Projects';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('project_id')->relationship('project', 'name')->required()->searchable()->preload(),
            TextInput::make('name')->required()->maxLength(255),
            Select::make('status')->options([
                    'planning' => 'Planning',
                    'active' => 'Active',
                    'completed' => 'Completed',
                ])->required(),
            DatePicker::make('start_date'),
            DatePicker::make('end_date'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('project.name')->label('Project')->sortable(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('start_date')->date('M d, Y')->sortable(),
            TextColumn::make('end_date')->date('M d, Y')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('project')->relationship('project', 'name')->searchable()->preload(),
            SelectFilter::make('status')->options([
                    'planning' => 'Planning',
                    'active' => 'Active',
                    'completed' => 'Completed',
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
            IssueRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSprints::route('/'),
            'create' => Pages\CreateSprint::route('/create'),
            'view'   => Pages\ViewSprint::route('/{record}'),
            'edit'   => Pages\EditSprint::route('/{record}/edit'),
        ];
    }
}