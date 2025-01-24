<?php

namespace Database\Factories\HelpCenter;

use App\Models\HelpCenter\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HelpCenter\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'section_id' => Section::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'body' => fake()->paragraph(),
            'is_public' => fake()->boolean(),
        ];
    }
}
