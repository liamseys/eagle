<?php

namespace App\Observers;

use App\Models\TicketField;

class TicketFieldObserver
{
    /**
     * Handle the Form "creating" event.
     */
    public function creating(TicketField $ticketField): void
    {
        $ticketField->sort = TicketField::where('ticket_id', $ticketField->ticket_id)->max('sort') + 1;
    }

    /**
     * Handle the TicketField "created" event.
     */
    public function created(TicketField $ticketField): void
    {
        //
    }

    /**
     * Handle the TicketField "updated" event.
     */
    public function updated(TicketField $ticketField): void
    {
        //
    }

    /**
     * Handle the TicketField "deleted" event.
     */
    public function deleted(TicketField $ticketField): void
    {
        //
    }

    /**
     * Handle the TicketField "restored" event.
     */
    public function restored(TicketField $ticketField): void
    {
        //
    }

    /**
     * Handle the TicketField "force deleted" event.
     */
    public function forceDeleted(TicketField $ticketField): void
    {
        //
    }
}
