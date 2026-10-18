<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'location',
        'phone',
        'email',
        'operating_hours',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function inquiries(): HasMany
    {
        return $this->hasMany(HospitalInquiry::class, 'department_id');
    }
}
