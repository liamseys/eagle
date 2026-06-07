<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\Tickets\SlaState;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared dashboard helpers: applies the dashboard filters (date range, client,
 * assignee, group) to a ticket query, plus the SLA compliance / risk maths reused
 * across the operational and performance widgets. Use alongside Filament's
 * InteractsWithPageFilters.
 */
trait InteractsWithTicketFilters
{
    protected function applyTicketFilters(Builder $query, bool $withDateRange = true): Builder
    {
        $filters = $this->filters ?? [];

        $startDate = $filters['startDate'] ?? null;
        $endDate = $filters['endDate'] ?? null;
        $clientId = $filters['clientId'] ?? null;
        $assigneeId = $filters['assigneeId'] ?? null;
        $groupId = $filters['groupId'] ?? null;

        return $query
            ->when($withDateRange && $startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($withDateRange && $endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($clientId, fn (Builder $query) => $query->where('requester_id', $clientId))
            ->when($assigneeId, fn (Builder $query) => $query->where('assignee_id', $assigneeId))
            ->when($groupId, fn (Builder $query) => $query->where('group_id', $groupId));
    }

    /**
     * On-time compliance for an SLA target over the filtered period.
     *
     * @return array{pct: int|null, met: int, total: int}
     */
    protected function ticketCompliance(string $dueColumn, string $achievedColumn): array
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

        $total = $met + $breached;

        return [
            'pct' => $total > 0 ? (int) round($met / $total * 100) : null,
            'met' => $met,
            'total' => $total,
        ];
    }

    /**
     * Currently-open tickets whose worst SLA state is breached / at risk.
     *
     * A 7-day horizon keeps the candidate set small (tickets due far out are always
     * on track); the precise state is resolved through the SLA engine so it matches
     * the badges shown elsewhere.
     *
     * @return array{0: int, 1: int} [breached, atRisk]
     */
    protected function openSlaRiskCounts(): array
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

        return [
            $candidates->filter(fn (Ticket $ticket): bool => $ticket->sla()->overallState() === SlaState::Breached)->count(),
            $candidates->filter(fn (Ticket $ticket): bool => $ticket->sla()->overallState() === SlaState::AtRisk)->count(),
        ];
    }
}
