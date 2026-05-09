<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $member = User::factory()->create([
            'name' => 'Team Member',
        ]);

        $workspaceA = Workspace::query()->create(['name' => 'Acme Workspace']);
        $workspaceB = Workspace::query()->create(['name' => 'Growth Workspace']);

        $workspaceA->users()->attach([
            $owner->getKey() => ['role' => 'owner'],
            $member->getKey() => ['role' => 'member'],
        ]);

        $workspaceB->users()->attach($owner->getKey(), ['role' => 'owner']);

        $owner->forceFill(['current_workspace_id' => $workspaceA->getKey()])->save();
        $member->forceFill(['current_workspace_id' => $workspaceA->getKey()])->save();

        $projectA = Project::query()->create([
            'workspace_id' => $workspaceA->getKey(),
            'name' => 'Launch Marketing Site',
            'description' => 'Public website and onboarding funnel.',
        ]);

        $projectB = Project::query()->create([
            'workspace_id' => $workspaceA->getKey(),
            'name' => 'Customer Portal',
            'description' => 'Self-service portal improvements.',
        ]);

        Task::query()->create([
            'workspace_id' => $workspaceA->getKey(),
            'project_id' => $projectA->getKey(),
            'assignee_id' => $member->getKey(),
            'title' => 'Draft landing copy',
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        Task::query()->create([
            'workspace_id' => $workspaceA->getKey(),
            'project_id' => $projectA->getKey(),
            'assignee_id' => $owner->getKey(),
            'title' => 'Review SEO metadata',
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        Task::query()->create([
            'workspace_id' => $workspaceA->getKey(),
            'project_id' => $projectB->getKey(),
            'assignee_id' => $member->getKey(),
            'title' => 'Fix profile form validation',
            'status' => 'done',
            'priority' => 'low',
            'due_date' => now()->subDays(1)->toDateString(),
        ]);
    }
}
