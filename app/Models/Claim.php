<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ClaimFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'policy_id',
    'policyholder_user_id',
    'incident_type',
    'incident_at',
    'location',
    'description',
])]
class Claim extends Model
{
    /** @use HasFactory<ClaimFactory> */
    use BelongsToTenant, HasFactory;

    /** @var array<string, string> */
    protected $attributes = [
        'status' => ClaimStatus::Draft->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ClaimStatus::class,
            'incident_at' => 'datetime',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function policyholder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'policyholder_user_id');
    }

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function submit(string $reference): void
    {
        $this->transitionTo(ClaimStatus::Submitted);
        $this->reference = $reference;
        $this->submitted_at = now();
    }

    public function transitionTo(ClaimStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new DomainException("A {$this->status->value} claim cannot transition to {$status->value}.");
        }

        $this->status = $status;
    }
}
