<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'integration_webhook_id',
    'event',
    'payload',
    'response_status',
    'response_body',
    'delivered_at',
])]
class IntegrationWebhookDelivery extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'delivered_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(IntegrationWebhook::class, 'integration_webhook_id');
    }
}
