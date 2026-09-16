<?php

namespace App\Filament\Resources;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CompanyTask;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class CompanyTaskResource extends Resource
{
    protected static ?string $model = CompanyTask::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static string|UnitEnum|null $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Company Tasks';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('department')
                    ->default('Operations')
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->options(collect(TaskPriority::cases())->mapWithKeys(fn ($p) => [$p->value => $p->label()]))
                    ->default(TaskPriority::Medium->value)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->default(TaskStatus::Pending->value)
                    ->required(),
                Forms\Components\Select::make('assignee_id')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('department')->badge()->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (TaskPriority $state): string => $state->color()),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TaskStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assignee'),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyTasks::route('/'),
            'create' => Pages\CreateCompanyTask::route('/create'),
            'edit' => Pages\EditCompanyTask::route('/{record}/edit'),
        ];
    }
}
