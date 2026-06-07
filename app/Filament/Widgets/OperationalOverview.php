<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use App\Models\Ticket;
use Closure;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * A single, hierarchy-driven snapshot of the support queue: the few numbers an
 * agent needs in the first few seconds, with urgency-coloured attention metrics
 * leading and supporting health metrics underneath.
 */
class OperationalOverview extends Widget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected string $view = 'filament.widgets.operational-overview';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        [$breached, $atRisk] = $this->openSlaRiskCounts();

        return [
            'breached' => $breached,
            'atRisk' => $atRisk,
            'unassigned' => $this->openCount(fn (Builder $query) => $query->whereNull('assignee_id')),
            'open' => $this->openCount(),
            'escalated' => $this->openCount(fn (Builder $query) => $query->where('is_escalated', true)),
            'firstResponse' => $this->ticketCompliance('first_response_due_at', 'first_responded_at'),
            'resolution' => $this->ticketCompliance('resolution_due_at', 'resolved_at'),
        ];
    }

    private function openCount(?Closure $constraint = null): int
    {
        $query = $this->applyTicketFilters(Ticket::query(), withDateRange: false)->unsolved();

        if ($constraint !== null) {
            $constraint($query);
        }

        return $query->count();
    }
}
