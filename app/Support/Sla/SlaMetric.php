<?php

namespace App\Support\Sla;

use App\Enums\Tickets\SlaState;
use Carbon\CarbonImmutable;

/**
 * The computed status of a single SLA target (first response or resolution).
 *
 * A pure, presentation-ready snapshot: it carries no logic for deciding the
 * state — that lives in the SlaStatusFactory.
 */
final class SlaMetric
{
    public function __construct(
        public readonly string $label,
        public readonly CarbonImmutable $dueAt,
        public readonly ?CarbonImmutable $achievedAt,
        public readonly SlaState $state,
        public readonly ?int $remainingMinutes,
    ) {}

    public function isAchieved(): bool
    {
        return $this->achievedAt !== null;
    }

    public function isBreached(): bool
    {
        return $this->state === SlaState::Breached;
    }

    public function isAtRisk(): bool
    {
        return $this->state === SlaState::AtRisk;
    }
}
