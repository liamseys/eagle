<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $name;

    public int $ticket_id_start;

    public bool $is_active;

    public array $support_email_addresses;

    public array $allowlisted_domains;

    public string $branding_primary_color;

    public string $branding_gradient_from_color;

    public string $branding_gradient_via_color;

    public string $branding_gradient_to_color;

    public static function group(): string
    {
        return 'general';
    }
}
