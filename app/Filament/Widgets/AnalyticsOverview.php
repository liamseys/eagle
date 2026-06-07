<?php

namespace App\Filament\Widgets;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use App\Models\Ticket;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class AnalyticsOverview extends Widget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected string $view = 'filament.widgets.analytics-overview';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $base = $this->applyTicketFilters(Ticket::query());

        $created = (clone $base)->count();
        $solved = (clone $base)->solved()->count();
        $unsolved = (clone $base)->unsolved()->count();

        return [
            'created' => $created,
            'solved' => $solved,
            'unsolved' => $unsolved,
            'solveRate' => $created > 0 ? (int) round($solved / $created * 100) : null,
            'createdTrend' => $this->createdTrend(),
            'distributions' => [
                ['title' => __('By priority'), 'rows' => $this->distribution(TicketPriority::cases(), 'priority')],
                ['title' => __('By type'), 'rows' => $this->distribution(TicketType::cases(), 'type')],
            ],
        ];
    }

    /**
     * Recent volume momentum: the last 30 days versus the previous 30. Only shown
     * on the unfiltered view, where a rolling comparison is meaningful.
     */
    private function createdTrend(): ?int
    {
        if ($this->isFiltered()) {
            return null;
        }

        $current = Ticket::query()->where('created_at', '>=', now()->subDays(30))->count();
        $previous = Ticket::query()->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();

        if ($previous === 0) {
            return null;
        }

        return (int) round(($current - $previous) / $previous * 100);
    }

    private function isFiltered(): bool
    {
        $filters = $this->filters ?? [];

        return filled($filters['startDate'] ?? null)
            || filled($filters['endDate'] ?? null)
            || filled($filters['clientId'] ?? null)
            || filled($filters['assigneeId'] ?? null)
            || filled($filters['groupId'] ?? null);
    }

    /**
     * @param  array<\BackedEnum>  $cases
     * @return array<int, array{label: string|null, color: string|array|null, count: int, pct: int}>
     */
    private function distribution(array $cases, string $column): array
    {
        $counts = $this->applyTicketFilters(Ticket::query())
            ->selectRaw("{$column}, COUNT(*) as total")
            ->groupBy($column)
            ->pluck('total', $column);

        $sum = (int) $counts->sum();

        return collect($cases)
            ->map(function ($case) use ($counts, $sum): array {
                $count = (int) $counts->get($case->value, 0);

                return [
                    'label' => $case->getLabel(),
                    'color' => $case->getColor(),
                    'count' => $count,
                    'pct' => $sum > 0 ? (int) round($count / $sum * 100) : 0,
                ];
            })
            ->all();
    }
}
