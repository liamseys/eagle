<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasColor, HasLabel
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case ON_HOLD = 'onhold';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OPEN => 'info',
            self::PENDING => 'warning',
            self::ON_HOLD => 'danger',
            self::RESOLVED => 'success',
            self::CLOSED => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::PENDING => 'Pending',
            self::ON_HOLD => 'On Hold',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }
}
