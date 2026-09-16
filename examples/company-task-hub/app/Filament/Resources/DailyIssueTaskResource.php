<?php

namespace App\Filament\Resources;

use App\Enums\IssueSeverity;
use App\Enums\TaskStatus;
use App\Models\DailyIssueTask;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class DailyIssueTaskResource extends Resource
{
    protected static ?string $model = DailyIssueTask::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;
    protected static string|UnitEnum|null $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Daily Issues';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('incident_type')
                    ->default('bug')
                    ->required(),
                Forms\Components\Select::make('severity')
                    ->options(collect(IssueSeverity::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->default(IssueSeverity::Major->value)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->default(TaskStatus::Pending->value)
                    ->required(),
                Forms\Components\Select::make('resolver_id')
                    ->relationship('resolver', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('resolved_at'),
                Forms\Components\Textarea::make('root_cause')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('resolution_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('incident_type')->badge(),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->color(fn (IssueSeverity $state): string => $state->color()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TaskStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('resolver.name')->label('Resolver'),
                Tables\Columns\TextColumn::make('resolved_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options(collect(IssueSeverity::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyIssueTasks::route('/'),
            'create' => Pages\CreateDailyIssueTask::route('/create'),
            'edit' => Pages\EditDailyIssueTask::route('/{record}/edit'),
        ];
    }
}
