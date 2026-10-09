<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalInquiryResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'author_name',
        'response_text',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(HospitalInquiry::class, 'inquiry_id');
    }
}
