<?php

namespace App\Services\Sla;

use App\Enums\Tickets\SlaState;
use App\Models\Ticket;
use App\Support\Sla\BusinessHours;
use App\Support\Sla\SlaConfiguration;
use App\Support\Sla\SlaMetric;
use App\Support\Sla\TicketSla;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Builds the live SLA status for a ticket from its stored deadlines and the
 * current time. This is the read/presentation seam of the SLA engine — it never
 * writes, and it is the single place where breach/at-risk rules live.
 */
final class SlaStatusFactory
{
    public function __construct(
        private readonly SlaConfiguration $configuration,
        private readonly BusinessHours $businessHours,
    ) {}

    public function for(Ticket $ticket): TicketSla
    {
        if (! $this->configuration->enabled()) {
            return new TicketSla;
        }

        $target = $this->configuration->targetFor($ticket->priority);

        if ($target === null) {
            return new TicketSla;
        }

        $now = CarbonImmutable::now();

        return new TicketSla(
            firstResponse: $this->metric(
                __('First response'),
                $ticket->first_response_due_at,
                $ticket->first_responded_at,
                $target->firstResponseMinutes,
                $now,
            ),
            resolution: $this->metric(
                __('Resolution'),
                $ticket->resolution_due_at,
                $ticket->resolved_at,
                $target->resolutionMinutes,
                $now,
            ),
        );
    }

    private function metric(string $label, ?CarbonInterface $dueAt, ?CarbonInterface $achievedAt, int $targetMinutes, CarbonImmutable $now): ?SlaMetric
    {
        if ($targetMinutes <= 0 || $dueAt === null) {
            return null;
        }

        $due = CarbonImmutable::instance($dueAt);

        // Achieved targets are settled: met if on time, breached if late.
        if ($achievedAt !== null) {
            $achieved = CarbonImmutable::instance($achievedAt);

            return new SlaMetric(
                $label,
                $due,
                $achieved,
                $achieved->lessThanOrEqualTo($due) ? SlaState::Met : SlaState::Breached,
                null,
            );
        }

        if ($now->greaterThan($due)) {
            return new SlaMetric($label, $due, null, SlaState::Breached, 0);
        }

        $remaining = $this->businessHours->diffInMinutes($now, $due);
        $threshold = (int) ceil($targetMinutes * $this->configuration->atRiskThresholdPercent() / 100);

        return new SlaMetric(
            $label,
            $due,
            null,
            $remaining <= $threshold ? SlaState::AtRisk : SlaState::OnTrack,
            $remaining,
        );
    }
}
