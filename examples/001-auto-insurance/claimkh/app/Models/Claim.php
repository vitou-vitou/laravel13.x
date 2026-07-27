<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'ref',
        'claimant_id',
        'adjuster_id',
        'type',
        'status',
        'incident_at',
        'lat',
        'lng',
        'address',
        'description',
        'estimated_resolution_date',
        'missing_docs',
    ];

    protected function casts(): array
    {
        return [
            'incident_at' => 'datetime',
            'estimated_resolution_date' => 'date',
            'missing_docs' => 'array',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjuster_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ClaimAuditLog::class);
    }
}
