<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\DailyIssueTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyIssueTask extends CreateRecord
{
    protected static string $resource = DailyIssueTaskResource::class;
}
