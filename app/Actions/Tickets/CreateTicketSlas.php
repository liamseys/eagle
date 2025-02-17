<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketSlaStatus;
use App\Enums\Tickets\TicketSlaType;
use App\Models\Ticket;
use App\Settings\WorkflowSettings;
use Illuminate\Support\Facades\DB;

final class CreateTicketSlas
{
    public function handle(Ticket $ticket): void
    {
        DB::transaction(function () use ($ticket): void {
            $this->closeSlas($ticket);
            $this->createSlas($ticket);
        });
    }

    /**
     * Create SLAs for a ticket.
     */
    public function createSlas(Ticket $ticket): void
    {
        $workflowSettings = app(WorkflowSettings::class);

        $slaPolicies = collect($workflowSettings->sla_policies);

        $slaPolicy = $slaPolicies->firstWhere('priority', $ticket->priority);

        $ticket->slas()->createMany([[
            'group_id' => $ticket->group_id,
            'type' => TicketSlaType::INITIAL_RESPONSE,
            'started_at' => now(),
            'expires_at' => now()->addMinutes((int) $slaPolicy['first_response_time']),
        ], [
            'group_id' => $ticket->group_id,
            'type' => TicketSlaType::NEXT_RESPONSE,
            'started_at' => now(),
            'expires_at' => now()->addMinutes((int) $slaPolicy['every_response_time']),
        ], [
            'group_id' => $ticket->group_id,
            'type' => TicketSlaType::RESOLUTION,
            'started_at' => now(),
            'expires_at' => now()->addMinutes((int) $slaPolicy['resolution_time']),
        ]]);
    }

    /**
     * Close the SLAs for a ticket.
     *
     * @return void
     */
    public function closeSlas(Ticket $ticket)
    {
        $ticketSlas = $ticket->slas;

        foreach ($ticketSlas as $ticketSla) {
            $ticketSla->update([
                'status' => TicketSlaStatus::CLOSED,
            ]);
        }
    }
}
