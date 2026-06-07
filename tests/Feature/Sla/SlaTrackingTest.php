<?php

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    config(['app.timezone_display' => 'UTC']);
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 10:00'));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function publicAgentComment(Ticket $ticket): void
{
    $ticket->comments()->create([
        'authorable_type' => User::class,
        'authorable_id' => User::factory()->create()->id,
        'body' => 'Agent reply',
        'is_public' => true,
    ]);
}

function requesterReply(Ticket $ticket): void
{
    $ticket->comments()->create([
        'authorable_type' => Client::class,
        'authorable_id' => $ticket->requester_id,
        'body' => 'Customer reply',
        'is_public' => true,
    ]);
}

it('assigns SLA deadlines when a ticket is created', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create([
        'priority' => TicketPriority::NORMAL,
        'created_at' => CarbonImmutable::parse('2026-06-08 09:00'),
    ])->fresh();

    expect($ticket->first_response_due_at)->not->toBeNull()
        ->and($ticket->resolution_due_at)->not->toBeNull()
        ->and($ticket->first_response_due_at->format('H:i'))->toBe('13:00');
});

it('records the first response on the first public agent reply only', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    publicAgentComment($ticket);
    $firstResponseAt = $ticket->fresh()->first_responded_at;

    CarbonImmutable::setTestNow(CarbonImmutable::now()->addHour());
    publicAgentComment($ticket);

    expect($firstResponseAt)->not->toBeNull()
        ->and($ticket->fresh()->first_responded_at->equalTo($firstResponseAt))->toBeTrue();
});

it('does not record a first response for internal notes or requester replies', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $ticket->comments()->create([
        'authorable_type' => User::class,
        'authorable_id' => User::factory()->create()->id,
        'body' => 'Internal note',
        'is_public' => false,
    ]);
    requesterReply($ticket);

    expect($ticket->fresh()->first_responded_at)->toBeNull();
});

it('marks the ticket resolved when it is solved and clears it when reopened', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $ticket->update(['status' => TicketStatus::SOLVED]);
    expect($ticket->fresh()->resolved_at)->not->toBeNull();

    $ticket->update(['status' => TicketStatus::OPEN]);
    expect($ticket->fresh()->resolved_at)->toBeNull();
});

it('recomputes deadlines when the priority changes', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'priority' => TicketPriority::LOW,
        'created_at' => CarbonImmutable::parse('2026-06-08 09:00'),
    ]);

    $lowDue = $ticket->fresh()->first_response_due_at;

    $ticket->update(['priority' => TicketPriority::URGENT]);
    $urgentDue = $ticket->fresh()->first_response_due_at;

    // Urgent (60m) is far sooner than Low (480m).
    expect($urgentDue->lessThan($lowDue))->toBeTrue()
        ->and($urgentDue->format('H:i'))->toBe('10:00');
});
