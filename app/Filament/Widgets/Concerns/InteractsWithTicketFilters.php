<?php

namespace App\Filament\Widgets\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Applies the shared dashboard filters (date range, client, assignee, group) to a
 * ticket query. Meant to be used alongside Filament's InteractsWithPageFilters.
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
}
