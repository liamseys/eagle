<?php

namespace Database\Factories;

use App\Models\TicketCommentTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketCommentTemplate>
 */
class TicketCommentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word(),
            'body' => fake()->paragraph(),
        ];
    }
}
