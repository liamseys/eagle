<?php

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketActivityColumn;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

/**
 * Create a comment authored by the ticket's requester.
 */
function requesterComment(Ticket $ticket): void
{
    $ticket->comments()->create([
        'authorable_type' => Client::class,
        'authorable_id' => $ticket->requester_id,
        'body' => 'Requester message',
        'is_public' => true,
    ]);
}

/**
 * Create a comment authored by an agent.
 */
function agentComment(Ticket $ticket, bool $isPublic): void
{
    $ticket->comments()->create([
        'authorable_type' => User::class,
        'authorable_id' => User::factory()->create()->id,
        'body' => 'Agent message',
        'is_public' => $isPublic,
    ]);
}

it('defaults a newly created ticket to New', function () {
    $client = Client::factory()->create();

    $ticket = $client->tickets()->create([
        'subject' => 'Help',
        'priority' => TicketPriority::NORMAL,
        'type' => TicketType::QUESTION,
    ]);

    expect($ticket->fresh()->status)->toBe(TicketStatus::NEW);
});

it('keeps a New ticket as New after the initial requester comment', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create();

    requesterComment($ticket);

    expect($ticket->fresh()->status)->toBe(TicketStatus::NEW);
});

it('keeps a New ticket as New after an initial agent comment', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create();

    agentComment($ticket, isPublic: true);

    expect($ticket->fresh()->status)->toBe(TicketStatus::NEW);
});

it('moves a New ticket to Pending when an agent replies to the requester', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create();

    // The inbound requester message creates the ticket and keeps it in the New queue.
    requesterComment($ticket);
    expect($ticket->fresh()->status)->toBe(TicketStatus::NEW);

    // The agent's public reply is a genuine response, so the ticket leaves New.
    agentComment($ticket, isPublic: true);
    expect($ticket->fresh()->status)->toBe(TicketStatus::PENDING);
});

it('moves a New ticket to Pending on a second public agent comment', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create();

    // The creation comment (e.g. an agent-created ticket) keeps the ticket New...
    agentComment($ticket, isPublic: true);
    expect($ticket->fresh()->status)->toBe(TicketStatus::NEW);

    // ...but a follow-up public reply moves it on to Pending.
    agentComment($ticket, isPublic: true);
    expect($ticket->fresh()->status)->toBe(TicketStatus::PENDING);
});

it('keeps a New ticket as New when an agent adds an internal note', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create();

    requesterComment($ticket);
    agentComment($ticket, isPublic: false);

    expect($ticket->fresh()->status)->toBe(TicketStatus::NEW);
});

it('reopens to Open when the requester replies', function (TicketStatus $status) {
    $ticket = Ticket::factory()->withStatus($status)->create();

    requesterComment($ticket);

    expect($ticket->fresh()->status)->toBe(TicketStatus::OPEN);
})->with([
    'pending' => TicketStatus::PENDING,
    'on hold' => TicketStatus::ON_HOLD,
    'solved' => TicketStatus::SOLVED,
]);

it('leaves an Open ticket Open when the requester replies', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    requesterComment($ticket);

    expect($ticket->fresh()->status)->toBe(TicketStatus::OPEN);
});

it('moves to Pending on a public agent reply', function (TicketStatus $status) {
    $ticket = Ticket::factory()->withStatus($status)->create();

    agentComment($ticket, isPublic: true);

    expect($ticket->fresh()->status)->toBe(TicketStatus::PENDING);
})->with([
    'open' => TicketStatus::OPEN,
    'on hold' => TicketStatus::ON_HOLD,
    'solved' => TicketStatus::SOLVED,
]);

it('does not change status on an internal agent note', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    agentComment($ticket, isPublic: false);

    expect($ticket->fresh()->status)->toBe(TicketStatus::OPEN)
        ->and($ticket->activity()->where('column', TicketActivityColumn::STATUS)->count())->toBe(0);
});

it('never changes status on a closed ticket', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::CLOSED)->create();

    requesterComment($ticket);
    agentComment($ticket, isPublic: true);

    expect($ticket->fresh()->status)->toBe(TicketStatus::CLOSED);
});

it('cannot move a ticket back to New', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    app(UpdateTicketStatus::class)->handle($ticket, TicketStatus::NEW);

    expect($ticket->fresh()->status)->toBe(TicketStatus::OPEN);
});

it('records an unattributed activity when no user is authenticated', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    app(UpdateTicketStatus::class)->handle($ticket, TicketStatus::SOLVED);

    $activity = $ticket->activity()
        ->where('column', TicketActivityColumn::STATUS)
        ->latest()
        ->first();

    expect($ticket->fresh()->status)->toBe(TicketStatus::SOLVED)
        ->and($activity->authorable_type)->toBeNull()
        ->and($activity->authorable_id)->toBeNull();
});

it('presents On Hold as Open to the requester', function () {
    expect(TicketStatus::ON_HOLD->requesterFacing())->toBe(TicketStatus::OPEN)
        ->and(TicketStatus::PENDING->requesterFacing())->toBe(TicketStatus::PENDING)
        ->and(TicketStatus::OPEN->requesterFacing())->toBe(TicketStatus::OPEN);
});
