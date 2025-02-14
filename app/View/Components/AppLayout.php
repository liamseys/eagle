<?php

namespace App\View\Components;

use App\Settings\GeneralSettings;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $generalSettings = app(GeneralSettings::class);

        return view('layouts.app', [
            'generalSettings' => $generalSettings,
        ]);
    }
}
