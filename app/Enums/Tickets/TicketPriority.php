<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketPriority: string implements HasColor, HasLabel
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::LOW => 'green',
            self::NORMAL => 'blue',
            self::HIGH => 'yellow',
            self::URGENT => 'red',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::NORMAL => 'Normal',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }
}
