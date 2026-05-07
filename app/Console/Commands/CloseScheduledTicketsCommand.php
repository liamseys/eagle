<?php

namespace App\Console\Commands;

use App\Enums\Tickets\TicketStatus;
use App\Jobs\CloseScheduledTicketJob;
use App\Models\Ticket;
use Illuminate\Console\Command;

class CloseScheduledTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:close-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close tickets that have been scheduled for closing in the past';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Ticket::query()
            ->whereNotNull('scheduled_close_at')
            ->where('scheduled_close_at', '<=', now())
            ->where('status', '!=', TicketStatus::CLOSED)
            ->get()
            ->each(fn (Ticket $ticket) => CloseScheduledTicketJob::dispatch($ticket));
    }
}
