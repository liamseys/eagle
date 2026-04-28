<?php

namespace App\Console\Commands;

use Database\Seeders\CannedResponseSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:install')]
#[Description('Seed the default permissions and canned responses required to run Eagle.')]
class InstallCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Installing Eagle...');

        $this->runSeeder('Seeding permissions', PermissionSeeder::class);
        $this->runSeeder('Seeding canned responses', CannedResponseSeeder::class);

        $this->newLine();
        $this->components->info('Eagle is ready to go.');

        return self::SUCCESS;
    }

    private function runSeeder(string $label, string $seeder): void
    {
        $this->components->task($label, fn (): bool => $this->callSilently('db:seed', [
            '--class' => $seeder,
            '--force' => true,
        ]) === self::SUCCESS);
    }
}
