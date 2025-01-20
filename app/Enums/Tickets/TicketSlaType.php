<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasLabel;

enum TicketSlaType: string implements HasLabel
{
    case INITIAL_RESPONSE = 'initial_response';
    case NEXT_RESPONSE = 'next_response';
    case RESOLUTION = 'resolution';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INITIAL_RESPONSE => 'Initial Response',
            self::NEXT_RESPONSE => 'Next Response',
            self::RESOLUTION => 'Resolution',
        };
    }
}
