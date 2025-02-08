<?php

namespace App\Observers;

use App\Actions\Tickets\CreateTicketSlas;
use App\Models\Ticket;
use App\Notifications\TicketCreated;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;

class TicketObserver
{
    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        DB::transaction(function () use ($ticket) {
            $generalSettings = app(GeneralSettings::class);

            $baseTicketId = 10000000;
            $ticket->ticket_id = $baseTicketId + $generalSettings->ticket_id_start;

            $generalSettings->ticket_id_start++;
            $generalSettings->save();
        });
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        app(CreateTicketSlas::class)->handle($ticket);

        if ($ticket->requester) {
            $ticket->requester->notify(new TicketCreated($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket->isDirty('group_id')) {
            app(CreateTicketSlas::class)->handle($ticket);
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
