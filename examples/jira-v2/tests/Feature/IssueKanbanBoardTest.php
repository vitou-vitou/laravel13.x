<?php

namespace Tests\Feature;

use App\Filament\Pages\IssueKanbanBoard;
use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class IssueKanbanBoardTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@jira.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_kanban_board_requires_authentication(): void
    {
        $this->get('/admin/issue-kanban-board')
            ->assertRedirect('/admin/login');
    }

    public function test_kanban_board_displays_issues_in_status_columns(): void
    {
        $user = $this->adminUser();
        $project = Project::query()->create([
            'name' => 'Platform',
            'key' => 'PLAT',
            'icon' => '🚀',
            'type' => 'kanban',
        ]);

        Issue::query()->create([
            'project_id' => $project->id,
            'key' => 'PLAT-1',
            'title' => 'Setup CI pipeline',
            'type' => 'task',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        Issue::query()->create([
            'project_id' => $project->id,
            'key' => 'PLAT-2',
            'title' => 'Fix login redirect',
            'type' => 'bug',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);

        Livewire::actingAs($user)
            ->test(IssueKanbanBoard::class)
            ->assertSee('Setup CI pipeline')
            ->assertSee('Fix login redirect')
            ->assertSee('To Do')
            ->assertSee('In Progress');
    }

    public function test_move_issue_updates_status(): void
    {
        $user = $this->adminUser();
        $project = Project::query()->create([
            'name' => 'Platform',
            'key' => 'PLAT',
            'icon' => '🚀',
            'type' => 'kanban',
        ]);

        $issue = Issue::query()->create([
            'project_id' => $project->id,
            'key' => 'PLAT-3',
            'title' => 'Write release notes',
            'type' => 'story',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        Livewire::actingAs($user)
            ->test(IssueKanbanBoard::class)
            ->call('moveIssue', $issue->id, 'in_progress')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'status' => 'in_progress',
        ]);
    }
}
