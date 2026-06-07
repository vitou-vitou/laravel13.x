<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Services\SupabaseActionLogger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        $transactionId = SupabaseActionLogger::generateTransactionId();

        return [
            'transaction_id' => $transactionId,
            'action' => fake()->optional()->slug(2, '_'),
            'status' => fake()->randomElement(ActivityLog::STATUSES),
            'message' => fake()->sentence(),
            'context' => ['source' => 'supabase', 'transaction_id' => $transactionId],
        ];
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => ActivityLog::STATUS_PROCESSING]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => ActivityLog::STATUS_PENDING]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => ActivityLog::STATUS_COMPLETED]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => ActivityLog::STATUS_FAILED]);
    }
}
