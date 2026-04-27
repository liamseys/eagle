<?php

namespace Database\Factories;

use App\Models\CannedResponseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CannedResponseCategory>
 */
class CannedResponseCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
