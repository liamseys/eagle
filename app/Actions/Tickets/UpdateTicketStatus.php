<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Notifications\TicketClosed;
use App\Notifications\TicketEscalationRequired;
use App\Notifications\TicketResolved;
use Illuminate\Support\Facades\DB;

final class UpdateTicketStatus
{
    /**
     * Execute the action.
     */
    public function handle(Ticket $ticket, TicketStatus $ticketStatus, array $attributes = [], bool $requireEscalation = false): void
    {
        DB::transaction(function () use ($ticket, $ticketStatus, $attributes, $requireEscalation) {
            $ticket->update([
                'status' => $ticketStatus,
            ]);

            $user = auth()->user();

            $ticket->activity()->create([
                'authorable_type' => get_class($user),
                'authorable_id' => $user->id,
                'column' => TicketActivityColumn::STATUS,
                'value' => $ticketStatus->value,
                'reason' => isset($attributes['reason']) ? $attributes['reason'] : null,
            ]);

            if ($ticket->requester) {
                $notificationDelay = now()->addMinutes(10);
                match ($ticketStatus) {
                    TicketStatus::RESOLVED => $ticket->requester->notify(
                        (new TicketResolved($ticket))->delay($notificationDelay)
                    ),
                    TicketStatus::CLOSED => $ticket->requester->notify(
                        (new TicketClosed($ticket))->delay($notificationDelay)
                    ),
                    default => null,
                };
            }

            // If escalation is required and the ticket has a requester, send the escalation notification.
            if ($requireEscalation && $ticket->requester) {
                $notificationDelay = now()->addMinutes(10);

                $ticket->requester->notify((new TicketEscalationRequired($ticket))->delay($notificationDelay));
            }
        });
    }
}
