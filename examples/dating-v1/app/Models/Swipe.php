<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Swipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'swiper_id',
        'swiped_id',
        'type', // like, pass, superlike
    ];

    public function swiper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swiper_id');
    }

    public function swiped(): BelongsTo
    {
        return $this->belongsTo(User::class, 'swiped_id');
    }
}
