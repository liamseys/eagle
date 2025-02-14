<?php

namespace App\Console\Commands;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketStatus;
use App\Jobs\CloseResolvedTicketJob;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseResolvedTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:close-resolved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close tickets that have been resolved for 48 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tickets = Ticket::whereStatus(TicketStatus::RESOLVED)
            ->whereHas('activity', function ($query) {
                $query->where('column', TicketActivityColumn::STATUS)
                    ->where('value', TicketStatus::RESOLVED);
            })
            ->get();

        foreach ($tickets as $ticket) {
            if ($ticket->activity->last()->created_at <= Carbon::now()->subHours(48)) {
                CloseResolvedTicketJob::dispatch($ticket, TicketStatus::CLOSED);
            }
        }
    }
}
