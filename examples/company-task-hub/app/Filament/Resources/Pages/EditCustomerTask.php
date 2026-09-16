<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\CustomerTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerTask extends EditRecord
{
    protected static string $resource = CustomerTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
