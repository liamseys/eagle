<?php

namespace App\Support\Sla;

/**
 * The first response and resolution targets (in business minutes) for a priority.
 *
 * A value of 0 for either metric means "no target", which the calculator treats
 * as no deadline for that metric.
 */
final class SlaTarget
{
    public function __construct(
        public readonly int $firstResponseMinutes,
        public readonly int $resolutionMinutes,
    ) {}

    public function hasFirstResponseTarget(): bool
    {
        return $this->firstResponseMinutes > 0;
    }

    public function hasResolutionTarget(): bool
    {
        return $this->resolutionMinutes > 0;
    }
}
