<?php

namespace App\Filament\Pages;

use App\Models\Issue;
use App\Models\Project;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IssueKanbanBoard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Kanban Board';

    protected static string|\UnitEnum|null $navigationGroup = 'Issues';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.issue-kanban-board';

    public ?int $projectId = null;

    /** @var array<string, array{label: string, color: string}> */
    public array $columns = [
        'todo' => ['label' => 'To Do', 'color' => 'gray'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'warning'],
        'in_review' => ['label' => 'In Review', 'color' => 'info'],
        'done' => ['label' => 'Done', 'color' => 'success'],
    ];

    public function mount(): void
    {
        $this->projectId = Project::query()->orderBy('name')->value('id');
    }

    /** @return Collection<int, Project> */
    public function getProjectsProperty(): Collection
    {
        return Project::query()->orderBy('name')->get();
    }

    /** @return array<string, Collection<int, Issue>> */
    public function getIssuesByStatusProperty(): array
    {
        $query = Issue::query()->with('project')->orderBy('key');

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        $issues = $query->get();

        $grouped = [];

        foreach (array_keys($this->columns) as $status) {
            $grouped[$status] = $issues->where('status', $status)->values();
        }

        return $grouped;
    }

    public function moveIssue(int $issueId, string $status): void
    {
        Validator::make(
            ['status' => $status],
            ['status' => ['required', Rule::in(array_keys($this->columns))]],
        )->validate();

        $issue = Issue::query()->findOrFail($issueId);
        $issue->update(['status' => $status]);

        Notification::make()
            ->title("{$issue->key} moved to {$this->columns[$status]['label']}")
            ->success()
            ->send();
    }
}
