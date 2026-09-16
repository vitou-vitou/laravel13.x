<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\DailyIssueTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDailyIssueTasks extends ListRecords
{
    protected static string $resource = DailyIssueTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
