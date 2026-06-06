<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('advanced.ticket_id_start', 1);
        $this->migrator->add('advanced.auto_close_solved_after_hours', 48);
    }
};
