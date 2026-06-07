<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\SlaState;
use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use App\Models\Ticket;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class SlaPerformanceOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected static ?int $sort = -5;

    protected ?string $heading = 'SLA performance';

    protected function getStats(): array
    {
        [$openBreached, $openAtRisk] = $this->openRiskCounts();

        return [
            $this->complianceStat(
                __('First response SLA'),
                $this->compliance('first_response_due_at', 'first_responded_at'),
            ),
            $this->complianceStat(
                __('Resolution SLA'),
                $this->compliance('resolution_due_at', 'resolved_at'),
            ),
            Stat::make(__('At risk'), number_format($openAtRisk))
                ->description(__('Open, approaching breach'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($openAtRisk > 0 ? 'warning' : 'gray'),
            Stat::make(__('Breached'), number_format($openBreached))
                ->description(__('Open, past an SLA target'))
                ->descriptionIcon('heroicon-m-fire')
                ->color($openBreached > 0 ? 'danger' : 'gray'),
        ];
    }

    /**
     * Compliance counts for a target over the filtered period.
     *
     * @return array{met: int, total: int}
     */
    private function compliance(string $dueColumn, string $achievedColumn): array
    {
        $base = $this->applyTicketFilters(Ticket::query())->whereNotNull($dueColumn);

        $met = (clone $base)
            ->whereNotNull($achievedColumn)
            ->whereColumn($achievedColumn, '<=', $dueColumn)
            ->count();

        $breached = (clone $base)
            ->where(function (Builder $query) use ($dueColumn, $achievedColumn) {
                $query
                    ->where(fn (Builder $query) => $query->whereNotNull($achievedColumn)->whereColumn($achievedColumn, '>', $dueColumn))
                    ->orWhere(fn (Builder $query) => $query->whereNull($achievedColumn)->where($dueColumn, '<', now()));
            })
            ->count();

        return ['met' => $met, 'total' => $met + $breached];
    }

    /**
     * @param  array{met: int, total: int}  $data
     */
    private function complianceStat(string $label, array $data): Stat
    {
        if ($data['total'] === 0) {
            return Stat::make($label, '—')
                ->description(__('No measured tickets yet'))
                ->color('gray');
        }

        $percentage = (int) round($data['met'] / $data['total'] * 100);

        return Stat::make($label, $percentage.'%')
            ->description(__(':met of :total on time', [
                'met' => number_format($data['met']),
                'total' => number_format($data['total']),
            ]))
            ->descriptionIcon($percentage >= 90 ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-trending-down')
            ->color(match (true) {
                $percentage >= 90 => 'success',
                $percentage >= 75 => 'warning',
                default => 'danger',
            });
    }

    /**
     * Count currently-open tickets whose worst SLA state is breached / at risk.
     *
     * A horizon keeps the candidate set small; tickets due far in the future are
     * always on track. The precise state is resolved through the SLA engine so it
     * matches the badges shown elsewhere.
     *
     * @return array{0: int, 1: int} [breached, atRisk]
     */
    private function openRiskCounts(): array
    {
        $horizon = now()->addDays(7);

        $candidates = $this->applyTicketFilters(Ticket::query(), withDateRange: false)
            ->unsolved()
            ->where(function (Builder $query) use ($horizon) {
                $query
                    ->where(fn (Builder $query) => $query->whereNull('first_responded_at')->whereNotNull('first_response_due_at')->where('first_response_due_at', '<=', $horizon))
                    ->orWhere(fn (Builder $query) => $query->whereNull('resolved_at')->whereNotNull('resolution_due_at')->where('resolution_due_at', '<=', $horizon));
            })
            ->get();

        $breached = $candidates->filter(fn (Ticket $ticket): bool => $ticket->sla()->overallState() === SlaState::Breached)->count();
        $atRisk = $candidates->filter(fn (Ticket $ticket): bool => $ticket->sla()->overallState() === SlaState::AtRisk)->count();

        return [$breached, $atRisk];
    }
}
