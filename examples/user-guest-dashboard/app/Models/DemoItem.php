<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoItem extends Model
{
    /** @use HasFactory<\Database\Factories\DemoItemFactory> */
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'title',
        'body',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
