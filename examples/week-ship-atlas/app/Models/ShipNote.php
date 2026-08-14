<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipNote extends Model
{
    public const WEEKDAYS = [
        'mon' => 'Mon — Region',
        'tue' => 'Tue — Company',
        'wed' => 'Wed — Project type',
        'thu' => 'Thu — Practice',
        'fri' => 'Fri — Publish',
        'sat' => 'Sat — Compare',
        'sun' => 'Sun — Clear',
    ];

    public const VERDICTS = ['pending', 'keep', 'drop'];

    protected $fillable = [
        'weekday',
        'title',
        'region',
        'company_habit',
        'project_type',
        'practice',
        'verdict',
    ];

    public function weekdayLabel(): string
    {
        return self::WEEKDAYS[$this->weekday] ?? $this->weekday;
    }
}
