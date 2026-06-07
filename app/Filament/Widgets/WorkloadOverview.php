<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use App\Models\Ticket;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WorkloadOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Workload';

    protected ?string $description = 'A snapshot of the open queue right now.';

    protected function getStats(): array
    {
        $open = $this->applyTicketFilters(Ticket::query(), withDateRange: false)->unsolved()->count();

        $unassigned = $this->applyTicketFilters(Ticket::query(), withDateRange: false)
            ->unsolved()
            ->whereNull('assignee_id')
            ->count();

        $escalated = $this->applyTicketFilters(Ticket::query(), withDateRange: false)
            ->unsolved()
            ->where('is_escalated', true)
            ->count();

        return [
            Stat::make(__('Open tickets'), number_format($open))
                ->description(__('Awaiting resolution'))
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('primary'),
            Stat::make(__('Unassigned'), number_format($unassigned))
                ->description(__('No agent assigned'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($unassigned > 0 ? 'warning' : 'gray'),
            Stat::make(__('Escalated'), number_format($escalated))
                ->description(__('Flagged as urgent'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($escalated > 0 ? 'danger' : 'gray'),
        ];
    }
}
