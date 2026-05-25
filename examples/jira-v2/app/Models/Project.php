<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'name',
        'key',
        'icon',
        'type',
    ];

    public function sprints(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    public function issues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Issue::class);
    }
}