<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\DailyIssueTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyIssueTask extends EditRecord
{
    protected static string $resource = DailyIssueTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
