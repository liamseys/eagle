<?php

namespace App\Services\Sla;

use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;

/**
 * Persists SLA tracking data on a ticket as its lifecycle unfolds.
 *
 * This is the only writer of the SLA columns. It is driven by observers, so the
 * SLA tracking logic stays out of the models, actions and UI. All writes are
 * "quiet" to avoid re-triggering ticket observers.
 */
final class SlaTracker
{
    public function __construct(private readonly SlaCalculator $calculator) {}

    /**
     * (Re)compute the deadlines for any target that has not yet been achieved.
     *
     * Safe to call on creation and whenever the priority changes.
     */
    public function assign(Ticket $ticket): void
    {
        $attributes = [];

        if ($ticket->first_responded_at === null) {
            $attributes['first_response_due_at'] = $this->calculator->firstResponseDueAt($ticket);
        }

        if ($ticket->resolved_at === null) {
            $attributes['resolution_due_at'] = $this->calculator->resolutionDueAt($ticket);
        }

        if ($attributes !== []) {
            // forceFill + saveQuietly: SLA columns are system-managed (not fillable)
            // and must not re-trigger the ticket observers.
            $ticket->forceFill($attributes)->saveQuietly();
        }
    }

    /**
     * Record the moment of the first public agent reply (only the first counts).
     */
    public function recordFirstResponse(Ticket $ticket): void
    {
        if ($ticket->first_responded_at !== null) {
            return;
        }

        $ticket->forceFill(['first_responded_at' => now()])->saveQuietly();
    }

    /**
     * Mark the ticket resolved when it reaches a solved state, or clear the marker
     * when it is reopened so the resolution clock resumes.
     */
    public function syncResolution(Ticket $ticket): void
    {
        $isResolved = in_array($ticket->status, [TicketStatus::SOLVED, TicketStatus::CLOSED], true);

        if ($isResolved && $ticket->resolved_at === null) {
            $ticket->forceFill(['resolved_at' => now()])->saveQuietly();

            return;
        }

        if (! $isResolved && $ticket->resolved_at !== null) {
            $ticket->forceFill(['resolved_at' => null])->saveQuietly();
        }
    }
}
