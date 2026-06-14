<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'url',
    'secret',
    'on_approved',
    'on_published',
    'is_active',
])]
class IntegrationWebhook extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'on_approved' => 'boolean',
            'on_published' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(IntegrationWebhookDelivery::class);
    }
}
