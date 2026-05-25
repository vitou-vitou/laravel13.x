<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsanaSeeder extends Seeder {
    public function run(): void {
        $projects = [
            [1, 'Website Launch', '#6366f1'],
            [2, 'Mobile App v2', '#10b981'],
            [3, 'Q3 Marketing', '#f59e0b'],
        ];

        $members = ['Alice', 'Bob', 'Carol', 'David', 'Eve'];
        $statuses = ['todo', 'in_progress', 'review', 'done'];
        $priorities = ['low', 'medium', 'high'];

        $taskTemplates = [
            ['Design mockups', 'Create wireframes and high-fidelity designs', 'high'],
            ['Backend API', 'Build REST endpoints for core features', 'high'],
            ['Write unit tests', 'Coverage must reach 80%', 'medium'],
            ['Code review', 'Review PRs from team members', 'medium'],
            ['Deploy to staging', 'Set up staging environment', 'low'],
            ['User interviews', 'Schedule 5 user research sessions', 'medium'],
            ['Update documentation', 'Keep API docs in sync', 'low'],
            ['Performance testing', 'Load test with 1000 concurrent users', 'high'],
            ['Security audit', 'Check OWASP top 10', 'high'],
            ['Launch checklist', 'Final pre-launch review', 'medium'],
        ];

        foreach ($projects as [$id, $name, $color]) {
            $pid = DB::table('projects')->insertGetId(['name' => $name, 'color' => $color, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
            foreach ($taskTemplates as $i => [$title, $desc, $priority]) {
                DB::table('tasks')->insert([
                    'project_id'  => $pid,
                    'title'       => $title,
                    'description' => $desc,
                    'assignee'    => $members[array_rand($members)],
                    'status'      => $statuses[$i % count($statuses)],
                    'priority'    => $priority,
                    'due_date'    => now()->addDays(rand(1, 30))->toDateString(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
