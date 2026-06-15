<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Creator;
use App\Models\User;

class CreatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Operator;
    }

    public function view(User $user, Creator $creator): bool
    {
        if ($user->role === UserRole::Operator) {
            return true;
        }

        return $user->role === UserRole::Creator
            && $user->creatorProfile?->id === $creator->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Operator;
    }

    public function update(User $user, Creator $creator): bool
    {
        return $user->role === UserRole::Operator;
    }
}
