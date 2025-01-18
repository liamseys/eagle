<?php

use App\Enums\Tickets\TicketPriority;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $slaPolicies = [];

        foreach (TicketPriority::cases() as $priority) {
            $slaPolicies[] = [
                'priority' => $priority->value,
                ...match ($priority) {
                    TicketPriority::LOW => [
                        'first_response_time' => 1440, // 24 hours in minutes
                        'every_response_time' => 2880, // 48 hours in minutes
                        'resolution_time' => 10080,   // 7 days in minutes
                    ],
                    TicketPriority::NORMAL => [
                        'first_response_time' => 480,  // 8 hours in minutes
                        'every_response_time' => 1440, // 24 hours in minutes
                        'resolution_time' => 4320,    // 3 days in minutes
                    ],
                    TicketPriority::HIGH => [
                        'first_response_time' => 60,   // 1 hour in minutes
                        'every_response_time' => 240,  // 4 hours in minutes
                        'resolution_time' => 1440,     // 1 day in minutes
                    ],
                    TicketPriority::URGENT => [
                        'first_response_time' => 15,   // 15 minutes
                        'every_response_time' => 60,   // 1 hour
                        'resolution_time' => 240,      // 4 hours
                    ],
                },
            ];
        }

        $this->migrator->add('workflow.sla_policies', $slaPolicies);
    }
};
