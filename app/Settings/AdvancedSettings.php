<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AdvancedSettings extends Settings
{
    public int $ticket_id_start;

    public int $auto_close_solved_after_hours;

    public static function group(): string
    {
        return 'advanced';
    }
}
