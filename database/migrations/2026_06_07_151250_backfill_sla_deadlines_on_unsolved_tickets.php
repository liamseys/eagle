<?php

use App\Models\Ticket;
use App\Services\Sla\SlaTracker;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Backfill SLA deadlines for existing unsolved tickets so the feature is
     * visible immediately. Best-effort: any failure (e.g. settings not yet
     * present) is reported rather than blocking the migration run.
     */
    public function up(): void
    {
        try {
            $tracker = app(SlaTracker::class);

            Ticket::withoutGlobalScopes()
                ->unsolved()
                ->whereNull('first_response_due_at')
                ->whereNull('resolution_due_at')
                ->chunkById(200, function ($tickets) use ($tracker): void {
                    foreach ($tickets as $ticket) {
                        $tracker->assign($ticket);
                    }
                });
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Intentionally irreversible: the SLA columns are dropped by the schema
     * migration, so there is nothing to undo here.
     */
    public function down(): void
    {
        //
    }
};
