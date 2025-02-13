<?php

namespace App\Mailboxes;

use App\Enums\Tickets\TicketType;
use App\Models\Client;
use App\Models\Ticket;
use BeyondCode\Mailbox\InboundEmail;

class TicketMailbox
{
    public function __invoke(InboundEmail $email, $ticketId = null)
    {
        if ($ticketId) {
            $this->addCommentToExistingTicket($ticketId, $email);

            return;
        }

        $this->createNewTicketWithComment($email);
    }

    private function addCommentToExistingTicket($ticketId, InboundEmail $email): void
    {
        $ticket = Ticket::where('ticket_id', $ticketId)->firstOrFail();

        $ticket->comments()->create([
            'authorable_type' => get_class($ticket->requester),
            'authorable_id' => $ticket->requester->id,
            'body' => $email->body() ?? '',
        ]);
    }

    private function createNewTicketWithComment(InboundEmail $email): void
    {
        $client = Client::firstOrCreate(
            ['email' => $email->from()],
            ['name' => $email->fromName()]
        );

        $ticket = $client->tickets()->create([
            'subject' => $email->subject(),
            'type' => TicketType::QUESTION,
        ]);

        $ticket->comments()->create([
            'authorable_type' => get_class($client),
            'authorable_id' => $client->id,
            'body' => $email->body() ?? '',
        ]);
    }
}
