<?php

namespace App\Filament\Resources\Workspaces\Pages;

use App\Filament\Resources\Workspaces\WorkspaceResource;
use App\Models\Workspace;
use App\Support\WorkspaceContext;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkspace extends CreateRecord
{
    protected static string $resource = WorkspaceResource::class;

    protected function handleRecordCreation(array $data): Workspace
    {
        /** @var Workspace $workspace */
        $workspace = static::getModel()::query()->create($data);
        $user = auth()->user();

        $workspace->users()->attach($user->getKey(), ['role' => 'owner']);
        WorkspaceContext::setForUser($user, $workspace);

        return $workspace;
    }
}
