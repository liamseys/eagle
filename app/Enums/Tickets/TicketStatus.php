<?php

namespace App\Enums\Tickets;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasColor, HasIcon, HasLabel
{
    case NEW = 'new';
    case OPEN = 'open';
    case PENDING = 'pending';
    case ON_HOLD = 'onhold';
    case SOLVED = 'solved';
    case CLOSED = 'closed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NEW => 'warning',
            self::OPEN => 'danger',
            self::PENDING => 'info',
            self::ON_HOLD => Color::Violet,
            self::SOLVED => 'gray',
            self::CLOSED => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NEW => 'New',
            self::OPEN => 'Open',
            self::PENDING => 'Pending',
            self::ON_HOLD => 'On Hold',
            self::SOLVED => 'Solved',
            self::CLOSED => 'Closed',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::NEW => 'heroicon-o-sparkles',
            self::OPEN => 'heroicon-o-envelope-open',
            self::PENDING => 'heroicon-o-clock',
            self::ON_HOLD => 'heroicon-o-pause-circle',
            self::SOLVED => 'heroicon-o-check-circle',
            self::CLOSED => 'heroicon-o-lock-closed',
        };
    }

    /**
     * The status as seen by the requester.
     *
     * On-hold is an internal status that requesters never see; to them it appears as Open.
     */
    public function requesterFacing(): self
    {
        return $this === self::ON_HOLD ? self::OPEN : $this;
    }
}
