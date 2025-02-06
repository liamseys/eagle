<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Component;

class Notes extends Component
{
    protected string $view = 'filament.forms.components.notes';

    public static function make(): static
    {
        return app(static::class);
    }
}
