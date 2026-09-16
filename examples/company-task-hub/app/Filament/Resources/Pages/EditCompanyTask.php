<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\CompanyTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyTask extends EditRecord
{
    protected static string $resource = CompanyTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
