<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.name', 'Eagle');
        $this->migrator->add('general.is_active', true);

        $this->migrator->add('general.support_email_addresses', []);

        $this->migrator->add('general.allowlisted_domains', []);

        $this->migrator->add('general.branding_primary_color', '#000000');
        $this->migrator->add('general.branding_primary_font', 'Lexend');
        $this->migrator->add('general.branding_gradient_from_color', '#f8cb09');
        $this->migrator->add('general.branding_gradient_via_color', '#eb2622');
        $this->migrator->add('general.branding_gradient_to_color', '#7506bf');
    }
};
