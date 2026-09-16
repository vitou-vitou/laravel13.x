<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workspace_id',
    'project_id',
    'assignee_id',
    'title',
    'description',
    'status',
    'priority',
    'due_date',
])]
class Task extends Model
{
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_TODO => 'To do',
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_DONE => 'Done',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
        ];
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function scopeForWorkspace(Builder $query, ?int $workspaceId): Builder
    {
        return $workspaceId
            ? $query->where('workspace_id', $workspaceId)
            : $query->whereRaw('1 = 0');
    }
}
