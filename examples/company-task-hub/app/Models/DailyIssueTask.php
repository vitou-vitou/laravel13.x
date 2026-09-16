<?php

namespace App\Models;

use App\Enums\IssueSeverity;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyIssueTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'incident_type',
        'severity',
        'status',
        'root_cause',
        'resolution_notes',
        'resolver_id',
        'resolved_at',
    ];

    protected $casts = [
        'severity' => IssueSeverity::class,
        'status' => TaskStatus::class,
        'resolved_at' => 'datetime',
    ];

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolver_id');
    }

    public function markResolved(?string $notes = null, ?string $rootCause = null, ?int $resolverId = null): self
    {
        $this->update([
            'status' => TaskStatus::Completed,
            'resolution_notes' => $notes ?? $this->resolution_notes,
            'root_cause' => $rootCause ?? $this->root_cause,
            'resolver_id' => $resolverId ?? $this->resolver_id,
            'resolved_at' => now(),
        ]);

        return $this;
    }
}
