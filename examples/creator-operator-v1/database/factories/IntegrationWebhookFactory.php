<?php

namespace Database\Factories;

use App\Models\IntegrationWebhook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntegrationWebhook>
 */
class IntegrationWebhookFactory extends Factory
{
    protected $model = IntegrationWebhook::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->operator(),
            'url' => 'https://example.com/webhooks/creator-operator',
            'secret' => 'test-secret',
            'on_approved' => true,
            'on_published' => true,
            'is_active' => true,
        ];
    }
}
