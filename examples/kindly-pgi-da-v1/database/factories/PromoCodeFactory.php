<?php

namespace Database\Factories;

use App\Enums\PromoCodeType;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PromoCode> */
class PromoCodeFactory extends Factory
{
    protected $model = PromoCode::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PROMO##')),
            'type' => PromoCodeType::Percent,
            'value' => 10,
            'max_uses' => null,
            'uses_count' => 0,
            'expires_at' => null,
            'is_active' => true,
        ];
    }
}
