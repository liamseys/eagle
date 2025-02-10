<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketPriority;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

final class UpdateTicketPriority
{
    /**
     * Execute the action.
     */
    public function handle(Ticket $ticket, TicketPriority $ticketPriority, array $attributes = []): void
    {
        DB::transaction(function () use ($ticket, $ticketPriority) {
            $ticket->update([
                'priority' => $ticketPriority,
            ]);

            $ticket->activity()->create([
                'user_id' => auth()->id(),
                'column' => TicketActivityColumn::PRIORITY,
                'value' => $ticketPriority->value,
            ]);
        });
    }
}
