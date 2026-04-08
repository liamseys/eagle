<?php

namespace Database\Factories\HelpCenter;

use App\Models\HelpCenter\Category;
use App\Services\Icons;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => fake()->randomElement(Icons::all()),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
