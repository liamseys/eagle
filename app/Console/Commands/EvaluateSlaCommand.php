<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaApproachingBreach;
use App\Notifications\SlaBreached;
use App\Support\Sla\SlaConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class EvaluateSlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sla:evaluate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify assignees about tickets approaching or past their SLA targets';

    /**
     * Execute the console command.
     */
    public function handle(SlaConfiguration $configuration): void
    {
        if (! $configuration->enabled()) {
            return;
        }

        Ticket::query()
            ->unsolved()
            ->where(fn ($query) => $query
                ->whereNotNull('first_response_due_at')
                ->orWhereNotNull('resolution_due_at'))
            ->with(['assignee', 'group.users'])
            ->chunkById(200, function (Collection $tickets): void {
                $tickets->each(fn (Ticket $ticket) => $this->evaluate($ticket));
            });
    }

    /**
     * Send any outstanding at-risk / breach alerts for a single ticket, once each.
     */
    private function evaluate(Ticket $ticket): void
    {
        $sla = $ticket->sla();
        $alerts = $ticket->sla_alerts ?? [];
        $sent = false;

        $metrics = [
            'first_response' => $sla->firstResponse,
            'resolution' => $sla->resolution,
        ];

        foreach ($metrics as $key => $metric) {
            // Settled targets never alert; only open ones can be at risk or breached.
            if ($metric === null || $metric->isAchieved()) {
                continue;
            }

            $type = match (true) {
                $metric->isBreached() => 'breached',
                $metric->isAtRisk() => 'at_risk',
                default => null,
            };

            if ($type === null || isset($alerts["{$key}.{$type}"])) {
                continue;
            }

            $recipients = $this->recipientsFor($ticket);

            // Nobody to tell yet; revisit on the next run once the ticket is owned.
            if ($recipients->isEmpty()) {
                continue;
            }

            Notification::send($recipients, $type === 'breached'
                ? new SlaBreached($ticket, $metric->label)
                : new SlaApproachingBreach($ticket, $metric->label));

            $alerts["{$key}.{$type}"] = now()->toIso8601String();
            $sent = true;
        }

        if ($sent) {
            // sla_alerts is system-managed (not fillable); write it directly.
            $ticket->forceFill(['sla_alerts' => $alerts])->saveQuietly();
        }
    }

    /**
     * The agents who should hear about a ticket's SLA: its assignee, else its group.
     *
     * @return Collection<int, User>
     */
    private function recipientsFor(Ticket $ticket): Collection
    {
        if ($ticket->assignee !== null) {
            return collect([$ticket->assignee]);
        }

        if ($ticket->group !== null) {
            return $ticket->group->users;
        }

        return collect();
    }
}
