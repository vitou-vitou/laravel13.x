<?php

namespace App\Models;

use App\Enums\OperatorPlan;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

#[Fillable(['name', 'email', 'password', 'role', 'operator_plan'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'operator_plan' => OperatorPlan::class,
        ];
    }

    public function integrationWebhooks(): HasMany
    {
        return $this->hasMany(IntegrationWebhook::class);
    }

    public function creatorLimit(): int
    {
        if (! $this->isOperator()) {
            return 0;
        }

        return app(\App\Services\OperatorBillingService::class)->creatorLimit($this);
    }

    public function creatorProfile(): HasOne
    {
        return $this->hasOne(Creator::class);
    }

    public function isOperator(): bool
    {
        return $this->role === UserRole::Operator;
    }

    public function isCreator(): bool
    {
        return $this->role === UserRole::Creator;
    }
}
