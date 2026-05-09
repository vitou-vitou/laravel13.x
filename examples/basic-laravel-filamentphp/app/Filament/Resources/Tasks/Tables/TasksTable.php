<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Models\User;
use App\Support\WorkspaceContext;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable(),
                TextColumn::make('assignee.name')
                    ->label('Assignee')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'todo' => 'Todo',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                    ]),
                SelectFilter::make('assignee_id')
                    ->label('Assignee')
                    ->options(fn () => User::query()
                        ->whereHas('workspaces', fn ($query) => $query->whereKey(WorkspaceContext::id()))
                        ->pluck('name', 'id')),
                Filter::make('due_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($query, $date) => $query->whereDate('due_date', '>=', $date))
                            ->when($data['until'] ?? null, fn ($query, $date) => $query->whereDate('due_date', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
