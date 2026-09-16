<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CompanyTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyTaskFactory extends Factory
{
    protected $model = CompanyTask::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'department' => fake()->randomElement(['Operations', 'Engineering', 'HR', 'Finance', 'Legal']),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'assignee_id' => User::factory(),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
        ];
    }
}
