<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\CustomerTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerTask extends CreateRecord
{
    protected static string $resource = CustomerTaskResource::class;
}
