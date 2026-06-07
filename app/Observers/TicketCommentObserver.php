<?php

namespace App\Observers;

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\Sla\SlaTracker;

class TicketCommentObserver
{
    /**
     * Handle the TicketComment "created" event.
     *
     * Applies Zendesk-style, comment-driven status transitions:
     * - a requester reply reopens a ticket that was waiting on them (or already solved);
     * - a public agent reply moves the ticket to Pending (waiting on the requester).
     *
     * New tickets are intentionally left untouched so the initial comment created alongside
     * a ticket (agent-created tickets, inbound email, the AI tool) keeps the New status.
     */
    public function created(TicketComment $comment): void
    {
        // Load the parent ticket explicitly (lazy loading is globally prevented) and without
        // global scopes, so the transition works regardless of who triggered the comment.
        $ticket = Ticket::withoutGlobalScopes()->find($comment->ticket_id);

        if ($ticket === null || $ticket->status === TicketStatus::CLOSED) {
            return;
        }

        if ($comment->authorable_type === User::class) {
            // A public agent reply is the SLA first response, even on a New ticket
            // whose status is intentionally left unchanged.
            if ($comment->is_public === true) {
                app(SlaTracker::class)->recordFirstResponse($ticket);
            }

            // Public agent reply -> Pending. Internal notes never change the status, and a
            // New ticket is left as New.
            if ($comment->is_public === true && in_array($ticket->status, [
                TicketStatus::OPEN,
                TicketStatus::ON_HOLD,
                TicketStatus::SOLVED,
            ], true)) {
                app(UpdateTicketStatus::class)->handle($ticket, TicketStatus::PENDING);
            }

            return;
        }

        // Requester reply -> reopen to Open when the ticket was waiting on them or solved.
        if (in_array($ticket->status, [
            TicketStatus::PENDING,
            TicketStatus::ON_HOLD,
            TicketStatus::SOLVED,
        ], true)) {
            app(UpdateTicketStatus::class)->handle($ticket, TicketStatus::OPEN);
        }
    }
}
