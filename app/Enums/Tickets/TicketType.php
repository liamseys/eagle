<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketType: string implements HasColor, HasIcon, HasLabel
{
    case QUESTION = 'question';
    case INCIDENT = 'incident';
    case PROBLEM = 'problem';
    case TASK = 'task';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::QUESTION => 'info',
            self::INCIDENT => 'warning',
            self::PROBLEM => 'danger',
            self::TASK => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::QUESTION => 'heroicon-o-chat-bubble-bottom-center-text',
            self::INCIDENT => 'heroicon-o-fire',
            self::PROBLEM => 'heroicon-o-puzzle-piece',
            self::TASK => 'heroicon-o-bolt',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::QUESTION => 'Question',
            self::INCIDENT => 'Incident',
            self::PROBLEM => 'Problem',
            self::TASK => 'Task',
        };
    }
}
