<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $table = 'sprints';

    protected $fillable = [
        'project_id',
        'name',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function issues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Issue::class);
    }
}