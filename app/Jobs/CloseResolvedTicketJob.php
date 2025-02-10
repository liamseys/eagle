<?php

namespace App\Jobs;

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CloseResolvedTicketJob implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;

    public TicketStatus $ticketStatus;

    /**
     * Create a new job instance.
     */
    public function __construct(Ticket $ticket, TicketStatus $ticketStatus)
    {
        $this->ticket = $ticket;
        $this->ticketStatus = $ticketStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(UpdateTicketStatus $updateTicketStatus): void
    {
        $updateTicketStatus->handle($this->ticket, $this->ticketStatus);
    }
}
