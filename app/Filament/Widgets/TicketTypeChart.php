<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketType;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Tickets by type';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $counts = Ticket::query()
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
