<?php

namespace App\Jobs;

use BeyondCode\Mailbox\Facades\Mailbox;
use BeyondCode\Mailbox\InboundEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportImapEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $host,
        private string $username,
        private string $password,
        private string $port,
        private string $folder,
        private string $processedFolder
    ) {
       
    }

    public function handle(): void
    {
        $mailbox = imap_open(
            "{{$this->host}:{$this->port}/imap/ssl}$this->folder",
            $this->username,
            $this->password
        );

        if (!$mailbox) {
            throw new \RuntimeException('Could not connect to IMAP server: ' . imap_last_error());
        }

        // Create processed folder if it doesn't exist
        if (!in_array($this->processedFolder, imap_list($mailbox, "{{$this->host}}", "*"))) {
            imap_createmailbox($mailbox, imap_utf7_encode("{{$this->host}}$this->processedFolder"));
        }

        $emails = imap_search($mailbox, 'ALL');

        if ($emails) {
            foreach ($emails as $emailNumber) {
                $structure = imap_fetchstructure($mailbox, $emailNumber);
                $headers = imap_headerinfo($mailbox, $emailNumber);
                
                // Get email content
                $body = '';
                if ($structure->type === 0) { // Plain text
                    $body = imap_fetchbody($mailbox, $emailNumber, 1);
                } elseif ($structure->type === 1) { // Multipart
                    $body = imap_fetchbody($mailbox, $emailNumber, 1.1);
                }

                // Create InboundEmail instance
                $message = "From: {$headers->fromaddress}\r\n";
                $message .= "To: {$headers->toaddress}\r\n";
                $message .= "Subject: {$headers->subject}\r\n";
                $message .= "Date: {$headers->date}\r\n\r\n";
                $message .= $body;

                $inboundEmail = InboundEmail::fromMessage($message);

                // Process the email using Laravel Mailbox
                Mailbox::callMailboxes($inboundEmail);

                // Move to processed folder
                imap_mail_move($mailbox, $emailNumber, $this->processedFolder);
            }
        }

        imap_expunge($mailbox);
        imap_close($mailbox);
    }
} 