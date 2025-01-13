<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Enums\Tickets\TicketStatus;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
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
                ->color('gray')
                ->modalWidth('lg')
                ->modalHeading(__('Escalate ticket'))
                ->modalDescription(__('When you escalate a ticket, it is marked as urgent and handled with top priority. A ticket can only be escalated once.'))
                ->modalSubmitActionLabel(__('Escalate'))
                ->form([
                    Textarea::make('reason')
                        ->label(__('Reason'))
                        ->placeholder(__('Enter the reason for escalating the ticket'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    //
                }),
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
