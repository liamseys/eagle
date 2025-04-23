<?php

namespace App\Filament\Client\Resources\TicketResource\Pages;

use App\Filament\Client\Resources\TicketResource;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
