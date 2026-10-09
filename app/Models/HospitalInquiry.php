<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HospitalInquiry extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const URGENCY_ROUTINE = 'routine';
    public const URGENCY_URGENT = 'urgent';
    public const URGENCY_CRITICAL = 'critical';

    protected $fillable = [
        'ticket_code',
        'patient_name',
        'email',
        'phone',
        'category',
        'urgency',
        'department_id',
        'subject',
        'message',
        'status',
        'staff_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public static function generateTicketCode(): string
    {
        do {
            $year = date('Y');
            $random = strtoupper(Str::random(5));
            $code = "HOSP-{$year}-{$random}";
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HospitalDepartment::class, 'department_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(HospitalInquiryResponse::class, 'inquiry_id')->oldest();
    }

    public function publicResponses(): HasMany
    {
        return $this->responses()->where('is_internal', false);
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query;
    }

    public function scopeFilterDepartment(Builder $query, ?int $departmentId): Builder
    {
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query;
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                  ->orWhere('patient_name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
