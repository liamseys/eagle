<?php

namespace Database\Factories;

use App\Enums\Forms\FormFieldType;
use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormField>
 */
class FormFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'type' => fake()->randomElement(FormFieldType::cases()),
            'name' => fake()->unique()->word(),
            'label' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'is_visible' => fake()->boolean(),
        ];
    }
}
