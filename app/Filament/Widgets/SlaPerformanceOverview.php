<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SlaPerformanceOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected static ?int $sort = 1;

    protected ?string $heading = 'SLA compliance';

    protected function getStats(): array
    {
        [$breached, $atRisk] = $this->openSlaRiskCounts();

        return [
            $this->complianceStat(__('First response SLA'), $this->ticketCompliance('first_response_due_at', 'first_responded_at')),
            $this->complianceStat(__('Resolution SLA'), $this->ticketCompliance('resolution_due_at', 'resolved_at')),
            Stat::make(__('At risk'), number_format($atRisk))
                ->description(__('Open, approaching breach'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($atRisk > 0 ? 'warning' : 'gray'),
            Stat::make(__('Breached'), number_format($breached))
                ->description(__('Open, past an SLA target'))
                ->descriptionIcon('heroicon-m-fire')
                ->color($breached > 0 ? 'danger' : 'gray'),
        ];
    }

    /**
     * @param  array{pct: int|null, met: int, total: int}  $data
     */
    private function complianceStat(string $label, array $data): Stat
    {
        if ($data['pct'] === null) {
            return Stat::make($label, '—')
                ->description(__('No measured tickets yet'))
                ->color('gray');
        }

        return Stat::make($label, $data['pct'].'%')
            ->description(__(':met of :total on time', [
                'met' => number_format($data['met']),
                'total' => number_format($data['total']),
            ]))
            ->descriptionIcon($data['pct'] >= 90 ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-trending-down')
            ->color(match (true) {
                $data['pct'] >= 90 => 'success',
                $data['pct'] >= 75 => 'warning',
                default => 'danger',
            });
    }
}
