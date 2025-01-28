<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.app_name', 'Eagle');
        $this->migrator->add('general.app_active', true);

        $this->migrator->add('general.branding_primary_color', '#000000');
        $this->migrator->add('general.branding_from_color', '#F8CB09');
        $this->migrator->add('general.branding_via_color', '#EB2622');
        $this->migrator->add('general.branding_to_color', '#7506BF');
    }
};
