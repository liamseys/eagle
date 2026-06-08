<?php

namespace App\Filament\Client\Widgets;

use App\Models\HelpCenter\Section;
use Filament\Widgets\Widget;

class CommonIssues extends Widget
{
    protected string $view = 'filament.client.widgets.common-issues';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        // A form is shown in the portal only when it is public AND explicitly featured for the
        // client portal. Sections appear only when they contain at least one such form, and only
        // those featured forms are eager-loaded so the view renders nothing else.
        $featuredForms = fn ($query) => $query
            ->public()
            ->where('settings->client_portal_featured', true);

        $sections = Section::query()
            ->whereHas('forms', $featuredForms)
            ->with(['forms' => fn ($query) => $featuredForms($query)->orderBy('sort')])
            ->get();

        return [
            'sections' => $sections,
        ];
    }
}
