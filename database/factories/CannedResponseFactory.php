<?php

namespace Database\Factories;

use App\Models\CannedResponse;
use App\Models\CannedResponseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CannedResponse>
 */
class CannedResponseFactory extends Factory
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
            'canned_response_category_id' => CannedResponseCategory::factory(),
            'title' => fake()->sentence(3),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'is_shared' => false,
            'last_used_at' => null,
        ];
    }

    public function shared(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_shared' => true,
        ]);
    }

    public function withoutCategory(): static
    {
        return $this->state(fn (array $attributes): array => [
            'canned_response_category_id' => null,
        ]);
    }
}
