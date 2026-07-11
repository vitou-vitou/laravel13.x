<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EndUser extends Model
{
    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'photo_url',
        'phone',
    ];

    public function tenantEndUsers(): HasMany
    {
        return $this->hasMany(TenantEndUser::class);
    }

    public function toProfileArray(): array
    {
        return [
            'telegram_id' => $this->telegram_id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'photo_url' => $this->photo_url,
            'phone_verified' => ! empty($this->phone),
        ];
    }
}
