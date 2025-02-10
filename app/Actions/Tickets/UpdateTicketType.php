<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketType;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

final class UpdateTicketType
{
    /**
     * Execute the action.
     */
    public function handle(Ticket $ticket, TicketType $ticketType, array $attributes = []): void
    {
        DB::transaction(function () use ($ticket, $ticketType) {
            $ticket->update([
                'type' => $ticketType,
            ]);

            $ticket->activity()->create([
                'user_id' => auth()->id(),
                'column' => TicketActivityColumn::TYPE,
                'value' => $ticketType->value,
            ]);
        });
    }
}
