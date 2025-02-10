<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Notifications\TicketEscalationRequired;
use Illuminate\Support\Facades\DB;

final class UpdateTicketStatus
{
    /**
     * Execute the action.
     */
    public function handle(Ticket $ticket, TicketStatus $ticketStatus, array $attributes = [], bool $requireEscalation = false): void
    {
        DB::transaction(function () use ($ticket, $ticketStatus, $requireEscalation) {
            $ticket->update([
                'status' => $ticketStatus,
            ]);

            $ticket->activity()->create([
                'user_id' => auth()->id(),
                'column' => TicketActivityColumn::STATUS,
                'value' => $ticketStatus->value,
            ]);

            if ($requireEscalation && $ticket->requester) {
                $notificationDelay = now()->addMinutes(10);

                $ticket->requester->notify((new TicketEscalationRequired($ticket))->delay($notificationDelay));
            }
        });
    }
}
