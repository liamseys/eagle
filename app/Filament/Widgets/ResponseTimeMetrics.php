<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use App\Models\Ticket;
use App\Support\Sla\BusinessHours;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResponseTimeMetrics extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Response & resolution times';

    protected ?string $description = 'Median time, counted in business hours.';

    protected function getStats(): array
    {
        $businessHours = app(BusinessHours::class);

        return [
            $this->timeStat(__('Median first response'), $this->medianBusinessMinutes('first_responded_at', $businessHours)),
            $this->timeStat(__('Median resolution'), $this->medianBusinessMinutes('resolved_at', $businessHours)),
        ];
    }

    private function timeStat(string $label, ?int $minutes): Stat
    {
        if ($minutes === null) {
            return Stat::make($label, '—')
                ->description(__('No measured tickets yet'))
                ->color('gray');
        }

        return Stat::make($label, $this->humanize($minutes))
            ->description(__('Business hours'))
            ->color('primary');
    }

    /**
     * Median business-hours minutes between creation and the achieved timestamp.
     */
    private function medianBusinessMinutes(string $achievedColumn, BusinessHours $businessHours): ?int
    {
        $minutes = $this->applyTicketFilters(Ticket::query())
            ->whereNotNull($achievedColumn)
            ->get(['created_at', $achievedColumn])
            ->map(fn (Ticket $ticket): int => $businessHours->diffInMinutes($ticket->created_at, $ticket->{$achievedColumn}))
            ->sort()
            ->values();

        if ($minutes->isEmpty()) {
            return null;
        }

        $count = $minutes->count();
        $middle = intdiv($count, 2);

        return $count % 2 === 1
            ? (int) $minutes[$middle]
            : (int) round(($minutes[$middle - 1] + $minutes[$middle]) / 2);
    }

    private function humanize(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return match (true) {
            $hours > 0 && $remaining > 0 => "{$hours}h {$remaining}m",
            $hours > 0 => "{$hours}h",
            default => "{$remaining}m",
        };
    }
}
