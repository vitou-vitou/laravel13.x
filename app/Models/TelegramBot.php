<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramBot extends Model
{
    protected $fillable = [
        'application_id',
        'bot_username',
        'bot_token',
        'domains',
    ];

    protected function casts(): array
    {
        return [
            'bot_token' => 'encrypted',
            'domains' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
