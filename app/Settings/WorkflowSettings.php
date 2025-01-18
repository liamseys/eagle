<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WorkflowSettings extends Settings
{
    public array $sla_policies;

    public static function group(): string
    {
        return 'workflow';
    }
}
