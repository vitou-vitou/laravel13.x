<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\Project;
use App\Models\User;
use App\Support\WorkspaceContext;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Select::make('project_id')
                    ->label('Project')
                    ->required()
                    ->options(fn () => Project::query()
                        ->where('workspace_id', WorkspaceContext::id())
                        ->orderBy('name')
                        ->pluck('name', 'id')),
                Select::make('assignee_id')
                    ->label('Assignee')
                    ->searchable()
                    ->preload()
                    ->options(fn () => User::query()
                        ->whereHas('workspaces', fn ($query) => $query->whereKey(WorkspaceContext::id()))
                        ->orderBy('name')
                        ->pluck('name', 'id')),
                Select::make('status')
                    ->options([
                        'todo' => 'Todo',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                    ])
                    ->default('todo')
                    ->required(),
                Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ])
                    ->default('medium')
                    ->required(),
                DatePicker::make('due_date'),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
