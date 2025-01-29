<?php

namespace Database\Factories;

use App\Models\HelpCenter\FormField;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketField>
 */
class TicketFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'form_field_id' => FormField::factory(),
            'value' => fake()->word(),
        ];
    }
}
