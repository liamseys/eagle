<?php

namespace App\Filament\Client\Resources\TicketResource\Pages;

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Filament\Client\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecordTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('closeTicket')
                ->label(__('Close ticket'))
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalDescription(__('Are you sure you would like to do this? Once the ticket is closed, it cannot be reopened.'))
                ->action(function (Ticket $record) {
                    app(UpdateTicketStatus::class)->handle(
                        $record,
                        TicketStatus::CLOSED,
                        ['reason' => 'The ticket was closed by requester.'],
                    );

                    $this->dispatch('ticket-closed');
                })
                ->hidden(fn (Ticket $record): bool => $record->status === TicketStatus::CLOSED),
        ];
    }
}
