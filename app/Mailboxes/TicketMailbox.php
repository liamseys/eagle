<?php

namespace App\Mailboxes;

use BeyondCode\Mailbox\InboundEmail;

class TicketMailbox
{
    public function __invoke(InboundEmail $email)
    {
        \Log::info($email->subject());
    }
}
