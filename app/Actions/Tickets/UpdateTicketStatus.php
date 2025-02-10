<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

final class UpdateTicketStatus
{
    /**
     * Execute the action.
     */
    public function handle(Ticket $ticket, TicketStatus $ticketStatus, array $attributes): void
    {
        DB::transaction(function () use ($ticket, $ticketStatus) {
            $ticket->update([
                'status' => $ticketStatus,
            ]);

            $ticket->activity()->create([
                'user_id' => auth()->id(),
                'column' => TicketActivityColumn::STATUS,
                'value' => $ticketStatus->value,
            ]);
        });
    }
}
