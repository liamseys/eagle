<?php

namespace Database\Factories\HelpCenter;

use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
