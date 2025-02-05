<?php

namespace App\Observers;

use App\Actions\Tickets\CreateTicketSla;
use App\Models\Ticket;
use App\Notifications\TicketCreated;

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
    public function created(Ticket $ticket, CreateTicketSla $createTicketSla): void
    {
        $createTicketSla->handle($ticket);

        if ($ticket->requester) {
            $ticket->requester->notify(new TicketCreated($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket, CreateTicketSla $createTicketSla): void
    {
        if ($ticket->isDirty('group_id')) {
            $createTicketSla->handle($ticket);
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
