<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Support\WorkspaceContext;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['workspace_id'] = WorkspaceContext::id();

        return $data;
    }
}
