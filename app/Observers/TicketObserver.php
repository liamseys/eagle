<?php

namespace App\Observers;

use App\Models\Ticket;

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
        $ticket->createSlas();
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket->isDirty('group_id')) {
            $ticket->closeSlas();

            $ticket->createSlas();
        }
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
