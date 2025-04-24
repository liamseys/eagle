<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ReportABug extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Report a bug';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.client.pages.report-a-bug';

    public function getTitle(): string|Htmlable
    {
        return __('Report a bug');
    }
}
