<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\WorkflowSettings;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageWorkflow extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Workflows';

    protected static ?string $slug = 'workflows';

    protected static ?string $title = 'Workflows';

    protected ?string $heading = 'Workflows';

    protected static string $settings = WorkflowSettings::class;

    protected static ?string $cluster = Settings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ...
            ]);
    }
}
