<?php

namespace App\Mailboxes;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Models\Client;
use App\Models\Ticket;
use App\Settings\GeneralSettings;
use BeyondCode\Mailbox\InboundEmail;
use Illuminate\Support\Str;

class TicketMailbox
{
    public function __invoke(InboundEmail $email, $ticketId = null)
    {
        $generalSettings = app(GeneralSettings::class);

        $supportEmailAddresses = array_map(
            fn ($item) => $item['email'],
            array_merge(
                [['label' => 'Default', 'email' => config('mail.from.address')]],
                $generalSettings->support_email_addresses
            )
        );

        if (! in_array($email->from(), $supportEmailAddresses)) {
            if ($ticketId) {
                $this->addCommentToExistingTicket($ticketId, $email);

                return;
            }

            $this->createNewTicketWithComment($email);
        }
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
            ['name' => $email->fromName(), 'password' => bcrypt(Str::random())]
        );

        $ticket = $client->tickets()->create([
            'subject' => $email->subject(),
            'priority' => TicketPriority::NORMAL,
            'type' => TicketType::QUESTION,
        ]);

        $ticket->comments()->create([
            'authorable_type' => get_class($client),
            'authorable_id' => $client->id,
            'body' => $email->body() ?? '',
        ]);
    }
}
