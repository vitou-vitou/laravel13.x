<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\CompanyTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyTasks extends ListRecords
{
    protected static string $resource = CompanyTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
