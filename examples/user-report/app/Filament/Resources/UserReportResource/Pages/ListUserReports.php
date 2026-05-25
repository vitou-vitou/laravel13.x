<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserReportResource\Pages;

use App\Filament\Resources\UserReportResource;
use Filament\Resources\Pages\ListRecords;

class ListUserReports extends ListRecords
{
    protected static string $resource = UserReportResource::class;

    /**
     * No header actions — this is a read-only report page.
     *
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
