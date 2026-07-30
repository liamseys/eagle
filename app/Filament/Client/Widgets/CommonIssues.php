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
        // A form is shown in the portal only when it is public, explicitly featured for the
        // client portal, and visible to the signed-in client (active, and not restricted to
        // groups the client does not belong to). Sections appear only when they contain at
        // least one such form, and only those forms are eager-loaded.
        $featuredForms = fn ($query) => $query
            ->public()
            ->visibleToViewer()
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
