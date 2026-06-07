<?php

namespace App\Support\Sla;

use App\Enums\Tickets\TicketPriority;
use App\Settings\WorkflowSettings;

/**
 * Read-only view over the SLA settings, decoupling the rest of the engine from
 * the raw settings shape.
 */
final class SlaConfiguration
{
    public function __construct(private readonly WorkflowSettings $settings) {}

    public function enabled(): bool
    {
        return $this->settings->sla_enabled;
    }

    public function atRiskThresholdPercent(): int
    {
        return $this->settings->sla_at_risk_threshold_percent;
    }

    public function targetFor(TicketPriority $priority): ?SlaTarget
    {
        $target = $this->settings->sla_targets[$priority->value] ?? null;

        if ($target === null) {
            return null;
        }

        $firstResponse = (int) ($target['first_response_minutes'] ?? 0);
        $resolution = (int) ($target['resolution_minutes'] ?? 0);

        if ($firstResponse <= 0 && $resolution <= 0) {
            return null;
        }

        return new SlaTarget($firstResponse, $resolution);
    }
}
