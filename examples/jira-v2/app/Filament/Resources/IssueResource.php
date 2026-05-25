<?php
namespace App\Filament\Resources;

use App\Filament\Resources\IssueResource\Pages;
use App\Filament\Resources\IssueResource\RelationManagers;
use App\Models\Issue;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{DatePicker, DateTimePicker, Select, Textarea, TextInput, Toggle};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\SelectFilter;

class IssueResource extends Resource
{
    protected static ?string $model = Issue::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bug-ant';

    protected static string|\UnitEnum|null $navigationGroup = 'Issues';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('project_id')->relationship('project', 'name')->required()->searchable()->preload(),
            Select::make('sprint_id')->relationship('sprint', 'project')->searchable()->preload(),
            TextInput::make('key')->required()->maxLength(255),
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('description')->rows(4)->columnSpanFull(),
            Select::make('type')->options([
                    'story' => 'Story',
                    'bug' => 'Bug',
                    'task' => 'Task',
                    'epic' => 'Epic',
                ])->required(),
            Select::make('status')->options([
                    'todo' => 'Todo',
                    'in_progress' => 'In progress',
                    'in_review' => 'In review',
                    'done' => 'Done',
                ])->required(),
            Select::make('priority')->options([
                    'lowest' => 'Lowest',
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'highest' => 'Highest',
                ])->required(),
            TextInput::make('assignee')->maxLength(255),
            TextInput::make('story_points')->numeric(),
            DatePicker::make('due_date'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('project.name')->label('Project')->sortable(),
            TextColumn::make('sprint.name')->label('Sprint')->sortable(),
            TextColumn::make('key')->searchable()->sortable(),
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('description')->limit(60)->wrap(),
            TextColumn::make('type')->badge()->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('priority')->badge()->sortable(),
            TextColumn::make('assignee')->searchable()->sortable(),
            TextColumn::make('story_points')->sortable(),
            TextColumn::make('due_date')->date('M d, Y')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('project')->relationship('project', 'name')->searchable()->preload(),
            SelectFilter::make('sprint')->relationship('sprint', 'name')->searchable()->preload(),
            SelectFilter::make('type')->options([
                    'story' => 'Story',
                    'bug' => 'Bug',
                    'task' => 'Task',
                    'epic' => 'Epic',
                ]),
            SelectFilter::make('status')->options([
                    'todo' => 'Todo',
                    'in_progress' => 'In progress',
                    'in_review' => 'In review',
                    'done' => 'Done',
                ]),
            SelectFilter::make('priority')->options([
                    'lowest' => 'Lowest',
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'highest' => 'Highest',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIssues::route('/'),
            'create' => Pages\CreateIssue::route('/create'),
            'view'   => Pages\ViewIssue::route('/{record}'),
            'edit'   => Pages\EditIssue::route('/{record}/edit'),
        ];
    }
}