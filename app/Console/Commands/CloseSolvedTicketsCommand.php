<?php

namespace App\Console\Commands;

use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketStatus;
use App\Jobs\CloseSolvedTicketJob;
use App\Models\Ticket;
use App\Settings\AdvancedSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseSolvedTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:close-solved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close tickets that have remained solved beyond the configured auto-close window';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $hours = app(AdvancedSettings::class)->auto_close_solved_after_hours;
        $threshold = Carbon::now()->subHours($hours);

        Ticket::query()
            ->where('status', TicketStatus::SOLVED->value)
            ->get()
            ->each(function (Ticket $ticket) use ($threshold): void {
                $solvedAt = $ticket->activity()
                    ->where('column', TicketActivityColumn::STATUS)
                    ->where('value', TicketStatus::SOLVED->value)
                    ->latest()
                    ->value('created_at');

                if ($solvedAt !== null && $solvedAt <= $threshold) {
                    CloseSolvedTicketJob::dispatch($ticket, TicketStatus::CLOSED);
                }
            });
    }
}
