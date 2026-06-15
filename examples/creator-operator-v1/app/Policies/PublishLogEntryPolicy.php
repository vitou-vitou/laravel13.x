<?php

namespace App\Policies;

use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\PublishLogEntry;
use App\Models\User;

class PublishLogEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PublishLogEntry $entry): bool
    {
        return $this->canAccessCreator($user, $entry->creator_id);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Operator;
    }

    public function update(User $user, PublishLogEntry $entry): bool
    {
        return $user->role === UserRole::Operator;
    }

    public function approve(User $user, PublishLogEntry $entry): bool
    {
        if (! $entry->status->isApprovable()) {
            return false;
        }

        if ($user->role === UserRole::Operator) {
            return true;
        }

        return $user->role === UserRole::Creator
            && $user->creatorProfile?->id === $entry->creator_id;
    }

    public function reject(User $user, PublishLogEntry $entry): bool
    {
        return $this->approve($user, $entry);
    }

    public function publish(User $user, PublishLogEntry $entry): bool
    {
        return $user->role === UserRole::Operator
            && $entry->status->isPublishable();
    }

    private function canAccessCreator(User $user, int $creatorId): bool
    {
        if ($user->role === UserRole::Operator) {
            return true;
        }

        return $user->role === UserRole::Creator
            && $user->creatorProfile?->id === $creatorId;
    }
}
