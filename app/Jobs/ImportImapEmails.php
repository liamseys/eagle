<?php

namespace App\Jobs;

use BeyondCode\Mailbox\Facades\Mailbox;
use BeyondCode\Mailbox\InboundEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;

class ImportImapEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $host,
        public string $username,
        public string $password,
        public int $port,
        public ?string $encryption,
        public string $folder,
        public string $processedFolder
    ) {
        $this->processedFolder = 'INBOX.'.$this->processedFolder;
    }

    public function handle(): void
    {
        $cm = new ClientManager;

        $client = $cm->make([
            'host' => $this->host,
            'port' => $this->port,
            'encryption' => $this->encryption,
            'validate_cert' => true,
            'username' => $this->username,
            'password' => $this->password,
            'protocol' => 'imap',
        ]);

        try {
            $client->connect();

            $sourceFolder = $client->getFolder($this->folder);
            $messages = $sourceFolder->messages()->all()->get();

            foreach ($messages as $message) {
                $headers = implode("\r\n", [
                    "From: {$message->getFrom()[0]->mail}",
                    "To: {$message->getTo()[0]->mail}",
                    "Subject: {$message->getSubject()}",
                    "Date: {$message->getDate()}",
                    '',
                    $message->getTextBody(),
                ]);

                $inboundEmail = InboundEmail::fromMessage($headers);
                Mailbox::callMailboxes($inboundEmail);
                $message->move($this->processedFolder);
            }

        } catch (ConnectionFailedException $e) {
            throw new \RuntimeException('Could not connect to IMAP server: '.$e->getMessage());
        } finally {
            if (isset($client)) {
                $client->disconnect();
            }
        }
    }
}
