<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\ClientPanelProvider;

return [
    AppServiceProvider::class,
    AppPanelProvider::class,
    ClientPanelProvider::class,
];
