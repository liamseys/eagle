<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Component;

class TicketComments extends Component
{
    protected string $view = 'filament.forms.components.ticket-comments';

    public static function make(): static
    {
        return app(static::class);
    }
}
