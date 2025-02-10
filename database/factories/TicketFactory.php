<?php

namespace Database\Factories;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Models\Client;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requester_id' => Client::factory(),
            'assignee_id' => User::factory(),
            'group_id' => Group::factory(),
            'subject' => fake()->sentence(),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'type' => fake()->randomElement(TicketType::cases()),
            'status' => fake()->randomElement(TicketStatus::cases()),
            'is_escalated' => fake()->boolean(),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
