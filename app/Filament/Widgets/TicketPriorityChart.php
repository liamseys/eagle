<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketPriority;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TicketPriorityChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Tickets by priority';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $counts = Ticket::query()
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $ticketPriorities = TicketPriority::cases();

        $data = collect($ticketPriorities)
            ->map(fn ($ticketPriority) => $counts->get($ticketPriority->value, 0))
            ->toArray();

        $labels = collect($ticketPriorities)
            ->map(fn ($ticketPriority) => $ticketPriority->getLabel())
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets by priority',
                    'data' => $data,
                    'backgroundColor' => [
                        '#4ade80',
                        '#60a5fa',
                        '#fbbf24',
                        '#f87171',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
