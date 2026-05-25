<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'email',
        'country',
        'city',
        'device_type',
        'signup_source',
        'avatar',
        'last_login_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];
}
