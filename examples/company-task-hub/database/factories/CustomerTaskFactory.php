<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CustomerTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerTaskFactory extends Factory
{
    protected $model = CustomerTask::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'customer_name' => fake()->company(),
            'customer_email' => fake()->companyEmail(),
            'task_type' => fake()->randomElement(['onboarding', 'support_ticket', 'contract_renewal', 'feature_request']),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => fake()->randomElement([TaskStatus::Pending, TaskStatus::InProgress, TaskStatus::Completed]),
            'notes' => fake()->paragraph(),
            'handler_id' => User::factory(),
            'due_date' => fake()->dateTimeBetween('now', '+14 days'),
        ];
    }
}
