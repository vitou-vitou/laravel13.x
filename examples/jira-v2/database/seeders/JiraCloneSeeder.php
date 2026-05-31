<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Database\Seeder;

class JiraCloneSeeder extends Seeder
{
    public function run(): void
    {
        $platform = Project::query()->create([
            'name' => 'Platform',
            'key' => 'PLAT',
            'icon' => '🚀',
            'type' => 'scrum',
        ]);

        $mobile = Project::query()->create([
            'name' => 'Mobile App',
            'key' => 'MOB',
            'icon' => '📱',
            'type' => 'kanban',
        ]);

        $sprint = Sprint::query()->create([
            'project_id' => $platform->id,
            'name' => 'Sprint 12',
            'status' => 'active',
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        $issues = [
            ['project_id' => $platform->id, 'sprint_id' => $sprint->id, 'key' => 'PLAT-101', 'title' => 'Setup CI pipeline', 'description' => 'Configure GitHub Actions for tests and deploy.', 'type' => 'task', 'status' => 'todo', 'priority' => 'high', 'assignee' => 'Alex', 'story_points' => 5],
            ['project_id' => $platform->id, 'sprint_id' => $sprint->id, 'key' => 'PLAT-102', 'title' => 'Fix login redirect loop', 'description' => 'Users bounce between /login and /dashboard.', 'type' => 'bug', 'status' => 'in_progress', 'priority' => 'highest', 'assignee' => 'Sam', 'story_points' => 3],
            ['project_id' => $platform->id, 'sprint_id' => $sprint->id, 'key' => 'PLAT-103', 'title' => 'Add audit log export', 'description' => 'CSV export for compliance team.', 'type' => 'story', 'status' => 'in_review', 'priority' => 'medium', 'assignee' => 'Jordan', 'story_points' => 8],
            ['project_id' => $platform->id, 'sprint_id' => $sprint->id, 'key' => 'PLAT-104', 'title' => 'Ship v2 release notes', 'description' => 'Draft and publish changelog.', 'type' => 'task', 'status' => 'done', 'priority' => 'low', 'assignee' => 'Alex', 'story_points' => 2],
            ['project_id' => $mobile->id, 'sprint_id' => null, 'key' => 'MOB-21', 'title' => 'Offline sync for drafts', 'description' => 'Persist unsent messages when offline.', 'type' => 'story', 'status' => 'todo', 'priority' => 'high', 'assignee' => 'Riley', 'story_points' => 13],
            ['project_id' => $mobile->id, 'sprint_id' => null, 'key' => 'MOB-22', 'title' => 'Crash on Android 14', 'description' => 'Repro on Pixel 8 when opening camera.', 'type' => 'bug', 'status' => 'in_progress', 'priority' => 'highest', 'assignee' => 'Sam', 'story_points' => 5],
            ['project_id' => $mobile->id, 'sprint_id' => null, 'key' => 'MOB-23', 'title' => 'Push notification opt-in', 'description' => 'Soft prompt after first successful action.', 'type' => 'task', 'status' => 'done', 'priority' => 'medium', 'assignee' => 'Jordan', 'story_points' => 3],
        ];

        foreach ($issues as $issue) {
            Issue::query()->create($issue);
        }
    }
}
