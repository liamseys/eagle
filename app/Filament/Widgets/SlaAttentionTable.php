<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TicketResource;
use App\Filament\Widgets\Concerns\InteractsWithTicketFilters;
use App\Models\Ticket;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SlaAttentionTable extends BaseWidget
{
    use InteractsWithPageFilters;
    use InteractsWithTicketFilters;

    protected static ?int $sort = -4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Needs attention'))
            ->description(__('Open tickets that are overdue or approaching an SLA target, most urgent first.'))
            ->query($this->getAttentionQuery())
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25])
            ->recordUrl(fn (Ticket $record): string => TicketResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('subject')
                    ->label(__('Ticket'))
                    ->weight(FontWeight::SemiBold)
                    ->limit(50)
                    ->description(fn (Ticket $record): string => '#'.$record->ticket_id.($record->requester ? '  ·  '.$record->requester->name : '')),
                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge(),
                TextColumn::make('sla')
                    ->label(__('SLA'))
                    ->badge()
                    ->state(fn (Ticket $record): ?string => $record->sla()->overallState()?->getLabel())
                    ->color(fn (Ticket $record): string => $record->sla()->overallState()?->getColor() ?? 'gray')
                    ->icon(fn (Ticket $record): ?string => $record->sla()->overallState()?->getIcon()),
                TextColumn::make('assignee.name')
                    ->label(__('Assignee'))
                    ->placeholder(__('Unassigned')),
                TextColumn::make('due')
                    ->label(__('Due'))
                    ->state(fn (Ticket $record): ?string => $this->soonestDue($record)),
            ])
            ->emptyStateHeading(__('All clear'))
            ->emptyStateDescription(__('No open tickets are overdue or approaching breach.'))
            ->emptyStateIcon('heroicon-o-shield-check');
    }

    protected function getAttentionQuery(): Builder
    {
        $horizon = now()->addDays(7);

        return $this->applyTicketFilters(Ticket::query(), withDateRange: false)
            ->with(['requester', 'assignee'])
            ->unsolved()
            ->where(function (Builder $query) use ($horizon) {
                $query
                    ->where(fn (Builder $query) => $query->whereNull('first_responded_at')->whereNotNull('first_response_due_at')->where('first_response_due_at', '<=', $horizon))
                    ->orWhere(fn (Builder $query) => $query->whereNull('resolved_at')->whereNotNull('resolution_due_at')->where('resolution_due_at', '<=', $horizon));
            })
            // Order by the soonest unmet deadline (a met / missing target is pushed to the far future).
            ->orderByRaw("LEAST(
                CASE WHEN first_responded_at IS NULL AND first_response_due_at IS NOT NULL THEN first_response_due_at ELSE '9999-12-31 23:59:59' END,
                CASE WHEN resolved_at IS NULL AND resolution_due_at IS NOT NULL THEN resolution_due_at ELSE '9999-12-31 23:59:59' END
            ) asc");
    }

    private function soonestDue(Ticket $ticket): ?string
    {
        $dues = [];

        if ($ticket->first_responded_at === null && $ticket->first_response_due_at) {
            $dues[] = $ticket->first_response_due_at;
        }

        if ($ticket->resolved_at === null && $ticket->resolution_due_at) {
            $dues[] = $ticket->resolution_due_at;
        }

        return collect($dues)->sort()->first()?->diffForHumans();
    }
}
