<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\CompanyTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyTask extends CreateRecord
{
    protected static string $resource = CompanyTaskResource::class;
}
