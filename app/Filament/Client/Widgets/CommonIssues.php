<?php

namespace App\Filament\Client\Widgets;

use App\Models\HelpCenter\Section;
use Filament\Widgets\Widget;

class CommonIssues extends Widget
{
    protected static string $view = 'filament.client.widgets.common-issues';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $sections = Section::whereHas('forms', function ($query) {
            $query->where('is_public', true)
                ->where('settings->client_portal_featured', true);
        })->get();

        return [
            'sections' => $sections,
        ];
    }
}
