<?php

namespace App\Actions\Tickets;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Notifications\TicketClosed;
use App\Notifications\TicketEscalationRequired;
use App\Notifications\TicketSolved;
use Illuminate\Support\Facades\DB;

final class UpdateTicketStatus
{
    /**
     * Execute the action.
     */
    public function handle(Ticket $ticket, TicketStatus $ticketStatus, array $attributes = [], bool $requireEscalation = false): void
    {
        // A ticket can never be moved back to New once it has left that state.
        if ($ticketStatus === TicketStatus::NEW) {
            return;
        }

        // Ensure the requester relation is available without a lazy-loading violation,
        // since this action runs from system contexts (jobs, commands, observers) too.
        $ticket->loadMissing('requester');

        DB::transaction(function () use ($ticket, $ticketStatus, $attributes, $requireEscalation) {
            $ticket->update([
                'status' => $ticketStatus,
            ]);

            // The actor may be null when the change is made by the system (queued jobs,
            // scheduled commands, inbound email), in which case the activity is unattributed.
            $user = auth()->user();

            $ticket->activity()->create([
                'authorable_type' => $user ? $user->getMorphClass() : null,
                'authorable_id' => $user?->getKey(),
                'column' => TicketActivityColumn::STATUS,
                'value' => $ticketStatus->value,
                'reason' => isset($attributes['reason']) ? $attributes['reason'] : null,
            ]);

            if ($ticket->requester) {
                $notificationDelay = now()->addMinutes(10);
                match ($ticketStatus) {
                    TicketStatus::SOLVED => $ticket->requester->notify(
                        (new TicketSolved($ticket))->delay($notificationDelay)
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
