<?php

namespace App\Services\Sla;

use App\Models\Ticket;
use App\Support\Sla\BusinessHours;
use App\Support\Sla\SlaConfiguration;
use App\Support\Sla\SlaTarget;
use Carbon\CarbonImmutable;

/**
 * Turns SLA targets into concrete, business-hours-aware deadlines for a ticket.
 *
 * Deadlines are measured from the ticket's creation time. The service performs no
 * persistence; it is the pure calculation seam of the SLA engine.
 */
final class SlaCalculator
{
    public function __construct(
        private readonly BusinessHours $businessHours,
        private readonly SlaConfiguration $configuration,
    ) {}

    public function firstResponseDueAt(Ticket $ticket): ?CarbonImmutable
    {
        $target = $this->targetFor($ticket);

        if ($target === null || ! $target->hasFirstResponseTarget()) {
            return null;
        }

        return $this->toStorageTimezone(
            $this->businessHours->addMinutes($ticket->created_at, $target->firstResponseMinutes)
        );
    }

    public function resolutionDueAt(Ticket $ticket): ?CarbonImmutable
    {
        $target = $this->targetFor($ticket);

        if ($target === null || ! $target->hasResolutionTarget()) {
            return null;
        }

        return $this->toStorageTimezone(
            $this->businessHours->addMinutes($ticket->created_at, $target->resolutionMinutes)
        );
    }

    /**
     * Deadlines are computed in the business timezone, but Eloquent persists a
     * datetime's wall-clock without converting timezones. Normalise to the app
     * (storage) timezone so the saved instant matches the real deadline instead
     * of drifting by the business-hours offset.
     */
    private function toStorageTimezone(CarbonImmutable $moment): CarbonImmutable
    {
        return $moment->setTimezone(config('app.timezone'));
    }

    private function targetFor(Ticket $ticket): ?SlaTarget
    {
        if (! $this->configuration->enabled()) {
            return null;
        }

        return $this->configuration->targetFor($ticket->priority);
    }
}
