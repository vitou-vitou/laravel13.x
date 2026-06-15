<?php

namespace Database\Factories;

use App\Enums\MusicPolicy;
use App\Enums\ServiceTier;
use App\Models\Creator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Creator>
 */
class CreatorFactory extends Factory
{
    protected $model = Creator::class;

    public function definition(): array
    {
        $handle = fake()->unique()->userName();

        return [
            'user_id' => null,
            'handle' => $handle,
            'tiktok_url' => 'https://www.tiktok.com/@'.$handle,
            'tier' => ServiceTier::Lite,
            'music_policy' => MusicPolicy::Skip,
            'youtube_manager_email' => fake()->safeEmail(),
            'meta_manager_email' => fake()->safeEmail(),
            'last_run_date' => null,
            'onboarding_notes' => null,
        ];
    }

    public function withUser(?User $user = null): static
    {
        return $this->state(function () use ($user) {
            $user ??= User::factory()->creator()->create();

            return [
                'user_id' => $user->id,
                'handle' => str_replace('.', '', $user->email),
            ];
        });
    }
}
