<?php

namespace App\Filament\Resources\DemoItems;

use App\Filament\Resources\DemoItems\Pages\CreateDemoItem;
use App\Filament\Resources\DemoItems\Pages\EditDemoItem;
use App\Filament\Resources\DemoItems\Pages\ListDemoItems;
use App\Filament\Resources\DemoItems\Schemas\DemoItemForm;
use App\Filament\Resources\DemoItems\Tables\DemoItemsTable;
use App\Models\DemoItem;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DemoItemResource extends Resource
{
    protected static ?string $model = DemoItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DemoItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DemoItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDemoItems::route('/'),
            'create' => CreateDemoItem::route('/create'),
            'edit' => EditDemoItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('guest_id', Filament::auth()->id());
    }
}
