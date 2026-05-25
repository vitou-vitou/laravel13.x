<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $table = 'issues';

    protected $fillable = [
        'project_id',
        'sprint_id',
        'key',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'assignee',
        'story_points',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sprint(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }
}