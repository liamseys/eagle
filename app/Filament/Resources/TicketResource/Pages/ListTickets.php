<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Enums\Tickets\TicketStatus;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('escalate')
                ->label(__('Escalate ticket'))
                ->color('gray'),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $ticketStatuses = TicketStatus::cases();

        $tabs = [
            Tab::make()
                ->label(__('All'))
                ->badge(Ticket::query()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query),
        ];

        foreach ($ticketStatuses as $ticketStatus) {
            $tabs[] = Tab::make()
                ->label($ticketStatus->getLabel())
                ->badge(Ticket::query()->where('status', $ticketStatus->value)->count())
                ->badgeColor($ticketStatus->getColor())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $ticketStatus->value));
        }

        return $tabs;
    }
}
