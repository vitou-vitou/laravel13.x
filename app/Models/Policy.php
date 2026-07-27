<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PolicyStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PolicyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'policyholder_user_id',
    'policy_number',
    'vehicle_make',
    'vehicle_model',
    'vehicle_year',
    'vehicle_plate',
    'coverage_amount_minor',
    'currency',
    'active_from',
    'active_to',
    'status',
])]
class Policy extends Model
{
    /** @use HasFactory<PolicyFactory> */
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'coverage_amount_minor' => 'integer',
            'currency' => Currency::class,
            'status' => PolicyStatus::class,
            'active_from' => 'date',
            'active_to' => 'date',
        ];
    }

    public function policyholder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'policyholder_user_id');
    }

    /**
     * Scope to policies that are active on a given date (defaults to today).
     */
    public function scopeActive(Builder $query, ?\DateTimeInterface $on = null): Builder
    {
        $on ??= now();

        return $query->where('status', PolicyStatus::Active->value)
            ->whereDate('active_from', '<=', $on)
            ->whereDate('active_to', '>=', $on);
    }

    /**
     * Scope to policies owned by a given policyholder.
     */
    public function scopeForPolicyholder(Builder $query, User|int $user): Builder
    {
        return $query->where('policyholder_user_id', $user instanceof User ? $user->id : $user);
    }

    public function isActive(?\DateTimeInterface $on = null): bool
    {
        $on ??= now();

        return $this->status === PolicyStatus::Active
            && $this->active_from <= $on
            && $this->active_to >= $on;
    }
}
