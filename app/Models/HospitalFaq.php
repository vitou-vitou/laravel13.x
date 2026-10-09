<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalFaq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'display_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'display_order' => 'integer',
    ];
}
