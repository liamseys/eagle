<?php

namespace App\Jobs;

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CloseScheduledTicketJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    /**
     * Execute the job.
     */
    public function handle(UpdateTicketStatus $updateTicketStatus): void
    {
        if ($this->ticket->status === TicketStatus::CLOSED) {
            return;
        }

        $updateTicketStatus->handle($this->ticket, TicketStatus::CLOSED, [
            'reason' => 'The ticket was automatically closed because it had been scheduled for closing.',
        ]);
    }
}
