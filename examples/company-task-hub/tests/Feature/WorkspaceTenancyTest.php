<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Support\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_and_resolves_active_workspace_for_a_user(): void
    {
        $user = User::factory()->create();
        $workspaceA = Workspace::query()->create(['name' => 'Workspace A']);
        $workspaceB = Workspace::query()->create(['name' => 'Workspace B']);

        $user->workspaces()->attach([
            $workspaceA->id => ['role' => 'member'],
            $workspaceB->id => ['role' => 'owner'],
        ]);

        $this->assertSame($workspaceA->id, WorkspaceContext::id($user));

        WorkspaceContext::setForUser($user, $workspaceB);
        $user->refresh();

        $this->assertSame($workspaceB->id, $user->current_workspace_id);
        $this->assertSame($workspaceB->id, WorkspaceContext::id($user));
    }

    public function test_it_scopes_projects_and_tasks_by_workspace(): void
    {
        $workspaceA = Workspace::query()->create(['name' => 'Workspace A']);
        $workspaceB = Workspace::query()->create(['name' => 'Workspace B']);
        $assignee = User::factory()->create();

        $projectA = Project::query()->create([
            'workspace_id' => $workspaceA->id,
            'name' => 'Project A',
        ]);

        $projectB = Project::query()->create([
            'workspace_id' => $workspaceB->id,
            'name' => 'Project B',
        ]);

        Task::query()->create([
            'workspace_id' => $workspaceA->id,
            'project_id' => $projectA->id,
            'assignee_id' => $assignee->id,
            'title' => 'Task A',
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
        ]);

        Task::query()->create([
            'workspace_id' => $workspaceB->id,
            'project_id' => $projectB->id,
            'assignee_id' => $assignee->id,
            'title' => 'Task B',
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
        ]);

        $this->assertSame(1, Project::query()->forWorkspace($workspaceA->id)->count());
        $this->assertSame(1, Task::query()->forWorkspace($workspaceA->id)->count());
        $this->assertSame(0, Project::query()->forWorkspace(null)->count());
        $this->assertSame(0, Task::query()->forWorkspace(null)->count());
    }
}
