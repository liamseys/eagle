<?php

namespace App\Observers;

use App\Enums\Tickets\TicketSlaType;
use App\Models\Ticket;
use App\Settings\WorkflowSettings;

class TicketObserver
{
    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        $ticket->ticket_id = random_int(10000000, 99999999);
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        $workflowSettings = app(WorkflowSettings::class);

        $slaPolicies = collect($workflowSettings->sla_policies);

        $slaPolicy = $slaPolicies->firstWhere('priority', $ticket->priority);

        $ticket->slas()->createMany([[
            'group_id' => $ticket->group_id,
            'type' => TicketSlaType::INITIAL_RESPONSE,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($slaPolicy['first_response_time']),
        ], [
            'group_id' => $ticket->group_id,
            'type' => TicketSlaType::NEXT_RESPONSE,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($slaPolicy['every_response_time']),
        ], [
            'group_id' => $ticket->group_id,
            'type' => TicketSlaType::RESOLUTION,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($slaPolicy['resolution_time']),
        ]]);
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
