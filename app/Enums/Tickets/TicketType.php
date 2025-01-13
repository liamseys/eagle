<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasLabel;

enum TicketType: string implements HasLabel
{
    case QUESTION = 'question';
    case INCIDENT = 'incident';
    case PROBLEM = 'problem';
    case TASK = 'task';

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
