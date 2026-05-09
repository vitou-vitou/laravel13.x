<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;

class Guest extends Model implements Authenticatable, FilamentUser
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;
    use \Illuminate\Auth\Authenticatable;
    use Authorizable;

    protected $fillable = [
        'uuid',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
        ];
    }

    public function demoItems(): HasMany
    {
        return $this->hasMany(DemoItem::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'guest';
    }
}
