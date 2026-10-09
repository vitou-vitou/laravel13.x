<?php

namespace App\Models;

use App\Events\QueueTokenUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'token_number',
        'patient_name',
        'patient_phone',
        'status',
        'counter_assigned',
        'called_at',
        'served_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'served_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function calculatePositionAhead(): int
    {
        if ($this->status !== 'waiting') {
            return 0;
        }

        return self::query()
            ->where('department_id', $this->department_id)
            ->where('status', 'waiting')
            ->where('id', '<', $this->id)
            ->count();
    }

    public function estimatedWaitMinutes(): int
    {
        $ahead = $this->calculatePositionAhead();

        return ($ahead + 1) * 8;
    }

    public static function generateNextTokenNumber(Department $department): string
    {
        $countToday = self::query()
            ->where('department_id', $department->id)
            ->whereDate('created_at', today())
            ->count();

        $sequence = str_pad((string) ($countToday + 1), 3, '0', STR_PAD_LEFT);

        return "{$department->code}-{$sequence}";
    }
}
