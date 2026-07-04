<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Vendor> */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id' => User::factory()->vendor(),
            'store_name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'status' => VendorStatus::Active,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => VendorStatus::Pending]);
    }
}
