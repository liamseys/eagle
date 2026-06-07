<?php

namespace App\Support\Sla;

use App\Settings\GeneralSettings;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Business-hours-aware time arithmetic.
 *
 * Wraps the weekly schedule configured in General Settings and exposes the two
 * operations the SLA engine needs: advancing a start time by a number of working
 * minutes, and measuring the working minutes between two instants. All math is
 * performed in the configured business timezone so that "09:00–17:00" is honoured
 * regardless of how timestamps are stored.
 */
final class BusinessHours
{
    /**
     * Normalised schedule keyed by lowercase English day name.
     *
     * @var array<string, array{open: bool, start: int, end: int}>
     */
    private array $days;

    /**
     * @param  array<string, mixed>  $schedule  The raw business_hours setting.
     */
    public function __construct(array $schedule, private readonly string $timezone)
    {
        $this->days = $this->normalise($schedule);
    }

    /**
     * Build from settings, evaluating the weekly schedule in the application's
     * configured timezone (`app.timezone_display`).
     *
     * Timestamps are always stored in UTC (`app.timezone`); this timezone is used
     * only to anchor the wall-clock business-hours window (e.g. "09:00–17:00")
     * during calculation. Callers convert the resulting instant back to UTC before
     * persisting it (see SlaCalculator). It is deliberately the same setting used
     * to display times, since that represents where the business operates.
     */
    public static function fromSettings(GeneralSettings $settings): self
    {
        return new self(
            $settings->business_hours,
            config('app.timezone_display') ?: config('app.timezone'),
        );
    }

    /**
     * Advance a start time by a number of business minutes.
     */
    public function addMinutes(CarbonInterface $start, int $minutes): CarbonImmutable
    {
        $cursor = CarbonImmutable::instance($start)->setTimezone($this->timezone);

        if ($minutes <= 0) {
            return $cursor;
        }

        // Without any open day the schedule cannot constrain anything; fall back to
        // linear time so callers never receive a null deadline or loop forever.
        if (! $this->hasOpenDay()) {
            return $cursor->addMinutes($minutes);
        }

        $remaining = $minutes;

        // A correct schedule resolves within a handful of weeks; the guard simply
        // prevents an unforeseen misconfiguration from spinning indefinitely.
        for ($guard = 0; $guard < 1000 && $remaining > 0; $guard++) {
            $cursor = $this->clampToOpen($cursor);
            $windowEnd = $this->windowEnd($cursor);
            $available = $this->minutesBetween($cursor, $windowEnd);

            if ($remaining <= $available) {
                return $cursor->addMinutes($remaining);
            }

            $remaining -= $available;
            $cursor = $windowEnd;
        }

        return $cursor;
    }

    /**
     * Count the business minutes between two instants (0 when $to is not after $from).
     */
    public function diffInMinutes(CarbonInterface $from, CarbonInterface $to): int
    {
        $from = CarbonImmutable::instance($from)->setTimezone($this->timezone);
        $to = CarbonImmutable::instance($to)->setTimezone($this->timezone);

        if ($to->lessThanOrEqualTo($from)) {
            return 0;
        }

        if (! $this->hasOpenDay()) {
            return $this->minutesBetween($from, $to);
        }

        $cursor = $from;
        $total = 0;

        for ($guard = 0; $guard < 5000 && $cursor->lessThan($to); $guard++) {
            $cursor = $this->clampToOpen($cursor);

            if ($cursor->greaterThanOrEqualTo($to)) {
                break;
            }

            $windowEnd = $this->windowEnd($cursor);
            $segmentEnd = $windowEnd->lessThan($to) ? $windowEnd : $to;
            $total += $this->minutesBetween($cursor, $segmentEnd);
            $cursor = $windowEnd;
        }

        return $total;
    }

    public function isOpenAt(CarbonInterface $time): bool
    {
        $time = CarbonImmutable::instance($time)->setTimezone($this->timezone);
        $day = $this->dayFor($time);

        if ($day === null || ! $day['open']) {
            return false;
        }

        $minuteOfDay = $time->hour * 60 + $time->minute;

        return $minuteOfDay >= $day['start'] && $minuteOfDay < $day['end'];
    }

    /**
     * Move the cursor forward to the next open instant (no-op when already open).
     */
    private function clampToOpen(CarbonImmutable $cursor): CarbonImmutable
    {
        for ($i = 0; $i < 8; $i++) {
            $day = $this->dayFor($cursor);

            if ($day !== null && $day['open']) {
                $start = $cursor->startOfDay()->addMinutes($day['start']);
                $end = $cursor->startOfDay()->addMinutes($day['end']);

                if ($cursor->lessThan($start)) {
                    return $start;
                }

                if ($cursor->lessThan($end)) {
                    return $cursor;
                }
            }

            $cursor = $cursor->addDay()->startOfDay();
        }

        return $cursor;
    }

    private function windowEnd(CarbonImmutable $cursor): CarbonImmutable
    {
        return $cursor->startOfDay()->addMinutes($this->dayFor($cursor)['end']);
    }

    private function minutesBetween(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return (int) (($to->getTimestamp() - $from->getTimestamp()) / 60);
    }

    /**
     * @return array{open: bool, start: int, end: int}|null
     */
    private function dayFor(CarbonImmutable $cursor): ?array
    {
        return $this->days[strtolower($cursor->format('l'))] ?? null;
    }

    private function hasOpenDay(): bool
    {
        foreach ($this->days as $day) {
            if ($day['open']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $schedule
     * @return array<string, array{open: bool, start: int, end: int}>
     */
    private function normalise(array $schedule): array
    {
        $days = [];

        foreach ($schedule as $name => $config) {
            $start = $this->toMinutes($config['start'] ?? null);
            $end = $this->toMinutes($config['end'] ?? null);
            $enabled = (bool) ($config['enabled'] ?? false);

            $days[strtolower((string) $name)] = [
                // A day only counts when enabled and the window is non-empty.
                'open' => $enabled && $end > $start,
                'start' => $start,
                'end' => $end,
            ];
        }

        return $days;
    }

    private function toMinutes(?string $time): int
    {
        if ($time === null || ! str_contains($time, ':')) {
            return 0;
        }

        [$hours, $minutes] = array_pad(explode(':', $time), 2, '0');

        return ((int) $hours) * 60 + (int) $minutes;
    }
}
