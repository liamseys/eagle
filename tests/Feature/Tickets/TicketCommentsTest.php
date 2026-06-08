<?php

use App\Enums\Tickets\TicketStatus;
use App\Livewire\TicketComments;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('renders the conversation with role labels for agents', function () {
    $this->actingAs(User::factory()->create());

    $client = Client::factory()->create(['name' => 'Reggie Jones']);
    $agent = User::factory()->create(['name' => 'Annie Smith']);
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create(['requester_id' => $client->id]);

    $ticket->comments()->create(['authorable_type' => Client::class, 'authorable_id' => $client->id, 'body' => '<p>Customer question</p>', 'is_public' => true]);
    $ticket->comments()->create(['authorable_type' => User::class, 'authorable_id' => $agent->id, 'body' => '<p>Public agent reply</p>', 'is_public' => true]);
    $ticket->comments()->create(['authorable_type' => User::class, 'authorable_id' => $agent->id, 'body' => '<p>Private internal note</p>', 'is_public' => false]);

    Livewire::test(TicketComments::class, ['ticket' => $ticket])
        ->assertSee('Reggie Jones')
        ->assertSee('Annie Smith')
        ->assertSee('Requester')
        ->assertSee('Agent')
        ->assertSee('Internal note')
        ->assertSee('Customer question')
        ->assertSee('Private internal note');
});

it('hides internal notes from non-agents', function () {
    $client = Client::factory()->create();
    $agent = User::factory()->create();
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create(['requester_id' => $client->id]);

    $ticket->comments()->create(['authorable_type' => Client::class, 'authorable_id' => $client->id, 'body' => '<p>Public question</p>', 'is_public' => true]);
    $ticket->comments()->create(['authorable_type' => User::class, 'authorable_id' => $agent->id, 'body' => '<p>Secret internal note</p>', 'is_public' => false]);

    Livewire::test(TicketComments::class, ['ticket' => $ticket])
        ->assertSee('Public question')
        ->assertDontSee('Secret internal note')
        ->assertDontSee('Internal note');
});

it('shows an empty state when there are no comments', function () {
    $this->actingAs(User::factory()->create());
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    Livewire::test(TicketComments::class, ['ticket' => $ticket])
        ->assertSee('No messages yet');
});
