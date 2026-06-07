<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WorkflowSettings extends Settings
{
    public bool $sla_enabled;

    /**
     * The percentage of the target remaining at which a target is flagged "at risk".
     */
    public int $sla_at_risk_threshold_percent;

    // First response and resolution targets, in business minutes, keyed by ticket
    // priority. Each entry is ['first_response_minutes' => int, 'resolution_minutes' => int].
    public array $sla_targets;

    public static function group(): string
    {
        return 'workflows';
    }
}
