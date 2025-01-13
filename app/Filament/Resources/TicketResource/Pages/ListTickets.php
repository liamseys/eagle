<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
}
