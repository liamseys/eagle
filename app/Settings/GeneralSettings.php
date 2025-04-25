<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $app_name;

    public string $app_path;

    public array $support_email_addresses;

    public array $allowlisted_domains;

    public $branding_favicon;

    public $branding_logo_black;

    public $branding_logo_white;

    public string $branding_primary_color;

    public string $branding_primary_font;

    public string $branding_gradient_from_color;

    public string $branding_gradient_via_color;

    public string $branding_gradient_to_color;

    public static function group(): string
    {
        return 'general';
    }
}
