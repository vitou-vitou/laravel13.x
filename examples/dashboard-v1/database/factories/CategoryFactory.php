<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => [
                'en' => ucfirst($name),
                'es' => ucfirst($name).' ES',
            ],
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
        ];
    }
}
