<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\CustomerTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerTasks extends ListRecords
{
    protected static string $resource = CustomerTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
