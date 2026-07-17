<?php

use App\Enums\Tickets\TicketStatus;
use App\Filament\Clusters\Settings\Resources\UserResource;
use App\Livewire\CreateTicketComment;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\MentionedInTicketComment;
use App\Support\RichEditor\AgentMentions;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

function mentionSpan(User $user): string
{
    return '<span data-type="mention" data-id="'.$user->id.'" data-char="@"></span>';
}

it('extracts unique mentioned user ids regardless of attribute order', function () {
    $content = '<p>Hey <span data-type="mention" data-id="abc" data-char="@"></span>'
        .' and <a data-id="def" data-type="mention">@Jane</a>'
        .' and again <span data-type="mention" data-id="abc"></span></p>';

    expect(AgentMentions::mentionedUserIds($content))->toBe(['abc', 'def'])
        ->and(AgentMentions::mentionedUserIds('<p>No mentions here</p>'))->toBe([])
        ->and(AgentMentions::mentionedUserIds(null))->toBe([]);
});

it('searches only active agents for mention suggestions', function () {
    User::factory()->create(['name' => 'John Active', 'is_active' => true]);
    User::factory()->create(['name' => 'John Inactive', 'is_active' => false]);
    User::factory()->create(['name' => 'Someone Else', 'is_active' => true]);

    $results = AgentMentions::provider()->getSearchResults('John');

    expect($results)->toHaveCount(1)
        ->and(array_values($results))->toBe(['John Active']);
});

it('notifies a mentioned agent once, even when mentioned multiple times', function () {
    $author = User::factory()->create(['is_active' => true]);
    $mentioned = User::factory()->create(['name' => 'Bobby Tables', 'is_active' => true]);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $this->actingAs($author);

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->fillForm([
            'comment' => '<p>Ping '.mentionSpan($mentioned).' and again '.mentionSpan($mentioned).'</p>',
        ])
        ->call('create');

    Notification::assertSentToTimes($mentioned, MentionedInTicketComment::class, 1);

    Notification::assertSentTo(
        $mentioned,
        MentionedInTicketComment::class,
        function (MentionedInTicketComment $notification, array $channels) use ($ticket): bool {
            expect($channels)->toContain('mail')->toContain('database')
                ->and($notification->commentUrl())
                ->toContain('/tickets/'.$ticket->id.'/edit')
                ->toContain('#comment-'.$notification->ticketComment->id);

            return true;
        },
    );
});

it('does not notify the author when they mention themselves', function () {
    $author = User::factory()->create(['is_active' => true]);
    $other = User::factory()->create(['is_active' => true]);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $this->actingAs($author);

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->fillForm([
            'comment' => '<p>Note to '.mentionSpan($author).' and '.mentionSpan($other).'</p>',
        ])
        ->call('create');

    Notification::assertNotSentTo($author, MentionedInTicketComment::class);
    Notification::assertSentToTimes($other, MentionedInTicketComment::class, 1);
});

it('does not notify inactive agents', function () {
    $author = User::factory()->create(['is_active' => true]);
    $inactive = User::factory()->create(['is_active' => false]);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $this->actingAs($author);

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->fillForm(['comment' => '<p>Ping '.mentionSpan($inactive).'</p>'])
        ->call('create');

    Notification::assertNotSentTo($inactive, MentionedInTicketComment::class);
});

it('stores the comment with the mention resolved to a named link', function () {
    $author = User::factory()->create(['is_active' => true]);
    $mentioned = User::factory()->create(['name' => 'Bobby Tables', 'is_active' => true]);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $this->actingAs($author);

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->fillForm(['comment' => '<p>Over to '.mentionSpan($mentioned).'</p>'])
        ->call('create');

    $comment = $ticket->comments()->first();

    expect($comment->body)
        ->toContain('Bobby Tables')
        ->toContain(UserResource::getUrl('edit', ['record' => $mentioned->id]));
});

it('renders the mention email button with legible white-on-dark styling', function () {
    $agent = User::factory()->create(['is_active' => true]);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();
    $comment = $ticket->comments()->create([
        'authorable_type' => User::class,
        'authorable_id' => $agent->id,
        'body' => '<p>Hello</p>',
        'is_public' => false,
    ]);

    $html = (new MentionedInTicketComment($comment))
        ->toMail(User::factory()->create())
        ->render();

    preg_match('/<a[^>]*class="button[^"]*"[^>]*>/', $html, $matches);
    $button = $matches[0] ?? '';

    // The generic body-link color previously overrode the button text color,
    // leaving dark text on the dark button. Inlined declarations apply in
    // order, so the last text-color declaration must be the white one.
    preg_match_all('/(?<!-)color:\s*(#[0-9a-f]+)/i', $button, $colors);

    expect($button)->not->toBe('')
        ->and($button)->toContain('background-color: #18181b')
        ->and($colors[1])->not->toBeEmpty()
        ->and(end($colors[1]))->toBe('#ffffff');
});

it('does not send mention notifications for client comments', function () {
    $client = Client::factory()->create();
    $agent = User::factory()->create(['is_active' => true]);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create(['requester_id' => $client->id]);

    $this->actingAs($client, 'client');

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->fillForm(['comment' => '<p>Hey '.mentionSpan($agent).'</p>'])
        ->call('create');

    Notification::assertNotSentTo($agent, MentionedInTicketComment::class);
});
