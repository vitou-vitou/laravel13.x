<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'age',
        'gender',
        'interested_in',
        'occupation',
        'city',
        'distance_km',
        'bio',
        'avatar_url',
        'photos',
        'interests',
    ];

    protected $casts = [
        'photos' => 'array',
        'interests' => 'array',
        'age' => 'integer',
        'distance_km' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
