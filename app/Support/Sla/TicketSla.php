<?php

namespace App\Support\Sla;

use App\Enums\Tickets\SlaState;

/**
 * The aggregate SLA status for a ticket, combining its individual targets.
 */
final class TicketSla
{
    public function __construct(
        public readonly ?SlaMetric $firstResponse = null,
        public readonly ?SlaMetric $resolution = null,
    ) {}

    public function isActive(): bool
    {
        return $this->firstResponse !== null || $this->resolution !== null;
    }

    /**
     * @return array<int, SlaMetric>
     */
    public function metrics(): array
    {
        return array_values(array_filter([$this->firstResponse, $this->resolution]));
    }

    /**
     * The most severe state across the active targets (null when no SLA applies).
     */
    public function overallState(): ?SlaState
    {
        $worst = null;

        foreach ($this->metrics() as $metric) {
            if ($worst === null || $metric->state->severity() > $worst->severity()) {
                $worst = $metric->state;
            }
        }

        return $worst;
    }
}
