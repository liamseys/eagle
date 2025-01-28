<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $app_name;

    public bool $app_active;

    public string $branding_primary_color;

    public string $branding_gradient_from_color;

    public string $branding_gradient_via_color;

    public string $branding_gradient_to_color;

    public static function group(): string
    {
        return 'general';
    }
}
