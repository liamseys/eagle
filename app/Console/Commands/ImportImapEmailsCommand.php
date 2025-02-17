<?php

namespace App\Console\Commands;

use App\Jobs\ImportImapEmails;
use Illuminate\Console\Command;

class ImportImapEmailsCommand extends Command
{
    protected $signature = 'imap:import';

    protected $description = 'Import emails from IMAP mailbox';

    public function handle(): void
    {
        if (! config('mail.imap.enabled')) {
            $this->info('IMAP is not enabled. Skipping email import.');

            return;
        }

        if (! config('mail.imap.host') || ! config('mail.imap.username') || ! config('mail.imap.password') || ! config('mail.imap.port') || ! config('mail.imap.folder') || ! config('mail.imap.processed_folder')) {
            $this->error('IMAP configuration is missing. Please check your mail.imap configuration.');

            return;
        }

        $this->info('Starting IMAP email import...');

        ImportImapEmails::dispatch(
            config('mail.imap.host'),
            config('mail.imap.username'),
            config('mail.imap.password'),
            config('mail.imap.port'),
            config('mail.imap.encryption'),
            config('mail.imap.folder', 'INBOX'),
            config('mail.imap.processed_folder', 'EagleProcessed')
        );

        $this->info('IMAP email import job has been dispatched.');
    }
}
