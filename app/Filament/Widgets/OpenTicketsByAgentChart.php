<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OpenTicketsByAgentChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected static ?int $sort = -2;

    protected ?string $heading = 'Open tickets by agent';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $rows = $this->applyTicketFilters(Ticket::query(), withDateRange: false)
            ->unsolved()
            ->selectRaw('assignee_id, COUNT(*) as total')
            ->groupBy('assignee_id')
            ->get();

        // Keep the busiest agents (and the unassigned bucket) so the chart stays readable.
        $counts = $rows
            ->mapWithKeys(fn ($row): array => [($row->assignee_id ?? '__unassigned') => (int) $row->total])
            ->sortDesc()
            ->take(8);

        $names = User::withoutGlobalScopes()
            ->whereIn('id', $counts->keys()->reject(fn ($key): bool => $key === '__unassigned')->all())
            ->pluck('name', 'id');

        $labels = $counts->keys()
            ->map(fn ($key): string => $key === '__unassigned' ? __('Unassigned') : ($names[$key] ?? __('Unknown')))
            ->all();

        return [
            'datasets' => [
                [
                    'label' => __('Open tickets'),
                    'data' => $counts->values()->all(),
                    'backgroundColor' => '#60a5fa',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
