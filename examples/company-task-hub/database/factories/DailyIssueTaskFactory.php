<?php

namespace Database\Factories;

use App\Enums\IssueSeverity;
use App\Enums\TaskStatus;
use App\Models\DailyIssueTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyIssueTaskFactory extends Factory
{
    protected $model = DailyIssueTask::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'incident_type' => fake()->randomElement(['bug', 'outage', 'performance', 'security', 'data_mismatch']),
            'severity' => fake()->randomElement(IssueSeverity::cases()),
            'status' => fake()->randomElement([TaskStatus::Pending, TaskStatus::InProgress, TaskStatus::Completed]),
            'root_cause' => fake()->optional()->sentence(),
            'resolution_notes' => fake()->optional()->paragraph(),
            'resolver_id' => User::factory(),
            'resolved_at' => null,
        ];
    }

    public function resolved(): self
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Completed,
            'root_cause' => fake()->sentence(),
            'resolution_notes' => fake()->paragraph(),
            'resolved_at' => now(),
        ]);
    }
}
