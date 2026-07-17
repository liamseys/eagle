<?php

use App\Actions\Tickets\GenerateTicketReply;
use App\Ai\Agents\TicketReplyDrafter;
use App\Enums\Tickets\TicketStatus;
use App\Livewire\CreateTicketComment;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Prompts\AgentPrompt;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

function agentReply(Ticket $ticket, User $agent, string $body, bool $isPublic = true): void
{
    $ticket->comments()->create([
        'authorable_type' => User::class,
        'authorable_id' => $agent->id,
        'body' => $body,
        'is_public' => $isPublic,
    ]);
}

it('learns the writing style from the agent\'s previous public replies on other tickets', function () {
    $agent = User::factory()->create();
    $otherAgent = User::factory()->create();

    $currentTicket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();
    $previousTicket = Ticket::factory()->withStatus(TicketStatus::SOLVED)->create();

    agentReply($previousTicket, $agent, '<p>Hi there! Happy to help. Cheers, Liam</p>');
    agentReply($previousTicket, $agent, '<p>A private note about billing.</p>', isPublic: false);
    agentReply($previousTicket, $otherAgent, '<p>Another agent wrote this.</p>');
    agentReply($currentTicket, $agent, '<p>A reply on the current ticket.</p>');

    $examples = (new TicketReplyDrafter($currentTicket, $agent))->styleExamples();

    expect($examples->all())->toBe(['Hi there! Happy to help. Cheers, Liam'])
        ->and((new TicketReplyDrafter($currentTicket, $agent))->instructions())
        ->toContain('Hi there! Happy to help. Cheers, Liam')
        ->not->toContain('Another agent wrote this')
        ->not->toContain('A private note about billing')
        ->not->toContain('A reply on the current ticket');
});

it('prompts with the ticket details and full conversation, and sanitizes the reply', function () {
    TicketReplyDrafter::fake(["```html\n<p>Hi <strong>Reggie</strong>,</p><script>alert(1)</script><p>We are on it.</p>\n```"]);

    $client = Client::factory()->create(['name' => 'Reggie Jones']);
    $agent = User::factory()->create(['name' => 'Annie Smith']);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'requester_id' => $client->id,
        'subject' => 'Cannot log in to my account',
    ]);

    $ticket->comments()->create([
        'authorable_type' => Client::class,
        'authorable_id' => $client->id,
        'body' => '<p>I forgot my password and the reset email never arrives.</p>',
        'is_public' => true,
    ]);
    agentReply($ticket, $agent, '<p>Check the spam folder before escalating.</p>', isPublic: false);

    $reply = app(GenerateTicketReply::class)->handle($ticket, $agent);

    expect($reply)->toBe('<p>Hi <strong>Reggie</strong>,</p>alert(1)<p>We are on it.</p>');

    TicketReplyDrafter::assertPrompted(function (AgentPrompt $prompt): bool {
        return $prompt->contains('Cannot log in to my account')
            && $prompt->contains('Customer Reggie Jones')
            && $prompt->contains('I forgot my password and the reset email never arrives.')
            && $prompt->contains('Internal note by Annie Smith')
            && $prompt->contains('Draft the next reply from the agent to the customer.');
    });
});

it('inserts the generated draft into the editor without sending anything', function () {
    TicketReplyDrafter::fake(['<p>Hi Reggie, thanks for reaching out!</p>']);

    $agent = User::factory()->create();
    $this->actingAs($agent);

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->callAction(TestAction::make('aiReply')->schemaComponent('comment'))
        ->assertSet('data.comment', '<p>Hi Reggie, thanks for reaching out!</p>')
        ->assertNotified(__('Draft ready'));

    // The draft is only placed in the editor; no comment is ever created.
    expect($ticket->comments()->count())->toBe(0);
});

it('asks for confirmation before replacing an existing draft', function () {
    TicketReplyDrafter::fake(['<p>The AI draft.</p>']);

    $this->actingAs(User::factory()->create());

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->fillForm(['comment' => '<p>My handwritten draft.</p>'])
        ->mountAction(TestAction::make('aiReply')->schemaComponent('comment'))
        ->assertActionMounted(TestAction::make('aiReply')->schemaComponent('comment'))
        ->callMountedAction()
        ->assertSet('data.comment', '<p>The AI draft.</p>');
});

it('generates immediately without a confirmation modal when the editor is empty', function () {
    TicketReplyDrafter::fake(['<p>The AI draft.</p>']);

    $this->actingAs(User::factory()->create());

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->mountAction(TestAction::make('aiReply')->schemaComponent('comment'))
        ->assertActionNotMounted(TestAction::make('aiReply')->schemaComponent('comment'))
        ->assertSet('data.comment', '<p>The AI draft.</p>');
});

it('shows an error notification when generation fails and keeps the draft untouched', function () {
    TicketReplyDrafter::fake(function (): never {
        throw new RuntimeException('Provider unavailable');
    });

    $this->actingAs(User::factory()->create());

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->fillForm(['comment' => '<p>My handwritten draft.</p>'])
        ->callAction(TestAction::make('aiReply')->schemaComponent('comment'))
        ->assertNotified(__('Could not generate a reply'))
        // fillForm stores the draft as a TipTap document, so match on content.
        ->assertSet('data.comment', fn ($value): bool => str_contains(json_encode($value), 'My handwritten draft.'));
});

it('shows the AI Reply button before Canned responses for agents', function () {
    $this->actingAs(User::factory()->create());

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $html = Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])->html();

    expect($html)->toContain('AI Reply')
        ->and(strpos($html, 'AI Reply'))->toBeLessThan(strpos($html, 'Canned responses'));
});

it('wires the AI Reply button with a loading state for the editor', function () {
    $this->actingAs(User::factory()->create());

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    $html = Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])->html();

    // The toolbar handler toggles the attribute the CSS overlay/spinner keys
    // off, carries the translated status message, and still mounts the action.
    expect($html)
        ->toContain('data-ai-reply-generating')
        ->toContain('Generating reply')
        ->toContain('fi-fo-rich-editor-tool-ai-reply')
        ->toContain('aiReply');
});

it('does not offer the AI Reply button to clients', function () {
    $client = Client::factory()->create();
    $this->actingAs($client, 'client');

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create(['requester_id' => $client->id]);

    Livewire::test(CreateTicketComment::class, ['ticket' => $ticket])
        ->assertDontSee('AI Reply');
});
