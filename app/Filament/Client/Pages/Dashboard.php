<?php

namespace App\Filament\Client\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Home';

    public function getHeading(): string
    {
        return '';
    }
}
