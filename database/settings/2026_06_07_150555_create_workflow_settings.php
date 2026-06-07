<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('workflows.sla_enabled', true);

        $this->migrator->add('workflows.sla_at_risk_threshold_percent', 20);

        // Targets are expressed in business minutes and keyed by ticket priority.
        $this->migrator->add('workflows.sla_targets', [
            'urgent' => ['first_response_minutes' => 60, 'resolution_minutes' => 240],
            'high' => ['first_response_minutes' => 120, 'resolution_minutes' => 480],
            'normal' => ['first_response_minutes' => 240, 'resolution_minutes' => 1440],
            'low' => ['first_response_minutes' => 480, 'resolution_minutes' => 2880],
        ]);
    }
};
