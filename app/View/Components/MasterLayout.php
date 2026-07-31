<?php

namespace App\View\Components;

use App\Settings\AdvancedSettings;
use App\Settings\GeneralSettings;
use Illuminate\View\Component;
use Illuminate\View\View;

class MasterLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.master', [
            'generalSettings' => app(GeneralSettings::class),
            'advancedSettings' => app(AdvancedSettings::class),
        ]);
    }
}
