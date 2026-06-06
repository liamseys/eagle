<?php

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Jobs\CloseSolvedTicketJob;
use App\Models\Ticket;
use App\Settings\AdvancedSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();

    $settings = app(AdvancedSettings::class);
    $settings->auto_close_solved_after_hours = 2;
    $settings->save();
});

/**
 * Mark a ticket solved at a point in the past.
 */
function solveTicketHoursAgo(Ticket $ticket, int $hoursAgo): void
{
    Carbon::setTestNow(now()->subHours($hoursAgo));
    app(UpdateTicketStatus::class)->handle($ticket, TicketStatus::SOLVED);
    Carbon::setTestNow();
}

it('dispatches a close job for tickets solved beyond the window', function () {
    Queue::fake();

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();
    solveTicketHoursAgo($ticket, hoursAgo: 3);

    $this->artisan('tickets:close-solved')->assertSuccessful();

    Queue::assertPushed(CloseSolvedTicketJob::class);
});

it('leaves recently solved tickets open', function () {
    Queue::fake();

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();
    solveTicketHoursAgo($ticket, hoursAgo: 1);

    $this->artisan('tickets:close-solved')->assertSuccessful();

    Queue::assertNotPushed(CloseSolvedTicketJob::class);
});
