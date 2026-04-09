<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketType;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TicketTypeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Tickets by type';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $clientId = $this->filters['clientId'] ?? null;

        $counts = Ticket::query()
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->when($clientId, fn ($query) => $query->where('requester_id', $clientId))
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $ticketTypes = TicketType::cases();

        $data = collect($ticketTypes)
            ->map(fn ($ticketType) => $counts->get($ticketType->value, 0))
            ->toArray();

        $labels = collect($ticketTypes)
            ->map(fn ($ticketType) => $ticketType->getLabel())
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets by type',
                    'data' => $data,
                    'backgroundColor' => [
                        '#60a5fa',
                        '#fbbf24',
                        '#f87171',
                        '#4ade80',
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
