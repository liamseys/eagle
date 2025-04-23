<?php

namespace App\Filament\Client\Resources\TicketResource\Pages;

use App\Filament\Client\Resources\TicketResource;
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
        return [];
    }
}
