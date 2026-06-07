<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Notifications\TicketCreated;
use App\Services\Sla\SlaTracker;
use App\Settings\AdvancedSettings;
use Illuminate\Support\Facades\DB;

class TicketObserver
{
    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        DB::transaction(function () use ($ticket) {
            $advancedSettings = app(AdvancedSettings::class);

            $baseTicketId = 10000000;
            $ticket->ticket_id = $baseTicketId + $advancedSettings->ticket_id_start;

            $advancedSettings->ticket_id_start++;
            $advancedSettings->save();
        });
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        app(SlaTracker::class)->assign($ticket);

        if ($ticket->requester) {
            $ticket->requester->notify(new TicketCreated($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket->wasChanged('status')) {
            app(SlaTracker::class)->syncResolution($ticket);
        }

        // Targets depend on priority, so recompute any deadline not yet met.
        if ($ticket->wasChanged('priority')) {
            app(SlaTracker::class)->assign($ticket);
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
