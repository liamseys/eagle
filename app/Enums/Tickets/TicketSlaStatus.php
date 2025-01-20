<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasLabel;

enum TicketSlaStatus: string implements HasLabel
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case CLOSED = 'closed';
    case ACTIVE_BREACHED = 'active_breached';
    case PAUSED_BREACHED = 'paused_breached';
    case CLOSED_BREACHED = 'closed_breached';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::PAUSED => 'Paused',
            self::CLOSED => 'Closed',
            self::ACTIVE_BREACHED => 'Active (breached)',
            self::PAUSED_BREACHED => 'Paused (breached)',
            self::CLOSED_BREACHED => 'Closed (breached)',
        };
    }
}
