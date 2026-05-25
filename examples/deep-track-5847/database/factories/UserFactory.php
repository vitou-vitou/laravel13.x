<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $countries = ['US', 'KH', 'TH', 'JP', 'FR', 'DE', 'AU', 'SG'];
        $cities = [
            'US' => ['New York', 'Los Angeles', 'Chicago'],
            'KH' => ['Phnom Penh', 'Siem Reap', 'Battambang'],
            'TH' => ['Bangkok', 'Chiang Mai', 'Phuket'],
            'JP' => ['Tokyo', 'Osaka', 'Kyoto'],
            'FR' => ['Paris', 'Lyon', 'Marseille'],
            'DE' => ['Berlin', 'Munich', 'Hamburg'],
            'AU' => ['Sydney', 'Melbourne', 'Brisbane'],
            'SG' => ['Singapore'],
        ];
        $country = $this->faker->randomElement($countries);
        $isActive = $this->faker->boolean(70);

        return [
            'username'      => $this->faker->unique()->userName(),
            'email'         => $this->faker->unique()->safeEmail(),
            'country'       => $country,
            'city'          => $this->faker->randomElement($cities[$country]),
            'device_type'   => $this->faker->randomElement(['web', 'mobile', 'tablet']),
            'signup_source' => $this->faker->randomElement(['organic', 'referral', 'social', 'paid']),
            'avatar'        => $this->faker->boolean(60) ? 'https://i.pravatar.cc/80?u=' . $this->faker->uuid() : null,
            'last_login_at' => $isActive
                ? $this->faker->dateTimeBetween('-29 days', 'now')
                : $this->faker->optional(0.7)->dateTimeBetween('-1 year', '-31 days'),
            'created_at'    => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
