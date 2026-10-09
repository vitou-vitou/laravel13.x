<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AppointmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code',
        'department_id',
        'patient_name',
        'patient_phone',
        'patient_email',
        'preferred_doctor',
        'preferred_date',
        'preferred_time_slot',
        'status',
        'confirmed_date',
        'confirmed_time_slot',
        'assigned_room',
        'staff_notes',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'confirmed_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public static function generateTrackingCode(): string
    {
        return 'APT-'.strtoupper(Str::random(8));
    }
}
