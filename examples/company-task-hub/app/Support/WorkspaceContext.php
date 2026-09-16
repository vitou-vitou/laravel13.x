<?php

namespace App\Support;

use App\Models\User;
use App\Models\Workspace;

class WorkspaceContext
{
    public static function current(?User $user = null): ?Workspace
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        if ($user->current_workspace_id && $user->belongsToWorkspace($user->current_workspace_id)) {
            return Workspace::query()->find($user->current_workspace_id);
        }

        return $user->workspaces()->first();
    }

    public static function id(?User $user = null): ?int
    {
        return static::current($user)?->getKey();
    }

    public static function setForUser(User $user, Workspace $workspace): void
    {
        if (! $user->belongsToWorkspace($workspace)) {
            return;
        }

        $user->forceFill([
            'current_workspace_id' => $workspace->getKey(),
        ])->save();
    }
}
