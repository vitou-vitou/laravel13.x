<?php

namespace App\Filament\Resources\DemoItems\Pages;

use App\Filament\Resources\DemoItems\DemoItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDemoItems extends ListRecords
{
    protected static string $resource = DemoItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
