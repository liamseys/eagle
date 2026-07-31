<?php

use App\Enums\Tickets\TicketStatus;
use App\Models\Client;
use App\Models\Group;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCommentByRequester;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

function replyAsRequester(Ticket $ticket): void
{
    $ticket->comments()->create([
        'authorable_type' => Client::class,
        'authorable_id' => $ticket->requester_id,
        'body' => 'Requester reply',
        'is_public' => true,
    ]);
}

it('notifies the assignee when the requester replies', function () {
    $assignee = User::factory()->create();
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'assignee_id' => $assignee->id,
    ]);

    replyAsRequester($ticket); // The creation comment does not notify.
    replyAsRequester($ticket);

    Notification::assertSentTo($assignee, TicketCommentByRequester::class);
});

it('notifies the group members when the requester replies to an unassigned ticket', function () {
    $group = Group::factory()->create();
    $groupMembers = User::factory()->count(2)->create();
    $group->users()->attach($groupMembers);

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'assignee_id' => null,
        'group_id' => $group->id,
    ]);

    replyAsRequester($ticket);
    replyAsRequester($ticket);

    Notification::assertSentTo($groupMembers, TicketCommentByRequester::class);
});

it('notifies only the assignee when the ticket also belongs to a group', function () {
    $assignee = User::factory()->create();
    $group = Group::factory()->create();
    $groupMember = User::factory()->create();
    $group->users()->attach($groupMember);

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'assignee_id' => $assignee->id,
        'group_id' => $group->id,
    ]);

    replyAsRequester($ticket);
    replyAsRequester($ticket);

    Notification::assertSentTo($assignee, TicketCommentByRequester::class);
    Notification::assertNotSentTo($groupMember, TicketCommentByRequester::class);
});

it('does not notify anyone for the ticket creation comment', function () {
    $assignee = User::factory()->create();
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create([
        'assignee_id' => $assignee->id,
    ]);

    replyAsRequester($ticket);

    Notification::assertNotSentTo($assignee, TicketCommentByRequester::class);
});

it('does not notify agents about their own comments', function () {
    $assignee = User::factory()->create();
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'assignee_id' => $assignee->id,
    ]);

    replyAsRequester($ticket);

    $ticket->comments()->create([
        'authorable_type' => User::class,
        'authorable_id' => $assignee->id,
        'body' => 'Agent reply',
        'is_public' => true,
    ]);

    Notification::assertNotSentTo($assignee, TicketCommentByRequester::class);
});

it('links to the agent panel even when generated from the client portal', function () {
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $comment = $ticket->comments()->create([
        'authorable_type' => Client::class,
        'authorable_id' => $ticket->requester_id,
        'body' => 'Requester reply',
        'is_public' => true,
    ]);

    $notification = new TicketCommentByRequester($comment);

    $expectedUrl = route('filament.app.resources.tickets.edit', ['record' => $ticket])
        .'#comment-'.$comment->id;

    expect($notification->commentUrl())->toBe($expectedUrl);

    $ticket->loadMissing('assignee');

    expect($notification->toDatabase($ticket->assignee))->toBeArray()
        ->and($notification->toMail($ticket->assignee)->actionUrl)->toBe($expectedUrl);
});

it('does not notify when a comment lands on a closed ticket', function () {
    $assignee = User::factory()->create();
    $ticket = Ticket::factory()->withStatus(TicketStatus::CLOSED)->create([
        'assignee_id' => $assignee->id,
    ]);

    replyAsRequester($ticket);
    replyAsRequester($ticket);

    Notification::assertNotSentTo($assignee, TicketCommentByRequester::class);
});
