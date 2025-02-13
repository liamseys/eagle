<?php

use App\Console\Commands\CloseResolvedTicketsCommand;
use BeyondCode\Mailbox\Console\CleanEmails;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(CleanEmails::class)->daily();
Schedule::command(CloseResolvedTicketsCommand::class)->daily();
