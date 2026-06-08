<?php

use App\Filament\Client\Resources\TicketResource\Pages\ListTickets;
use App\Models\Client;
use App\Models\Ticket;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $this->client = Client::factory()->create();
    $this->actingAs($this->client, 'client');
});

it('renders the portal overview with the subject-led layout', function () {
    $tickets = Ticket::factory()->count(3)->create(['requester_id' => $this->client->id]);

    Livewire::test(ListTickets::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($tickets)
        ->assertCanRenderTableColumn('subject')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('priority');
});

it('omits agent-specific columns from the portal overview', function () {
    Ticket::factory()->create(['requester_id' => $this->client->id]);

    Livewire::test(ListTickets::class)
        ->assertSuccessful()
        // SLA is internal and the standalone ticket-id column is folded into the subject meta line.
        ->assertTableColumnDoesNotExist('sla_status')
        ->assertTableColumnDoesNotExist('ticket_id');
});

it('shows the client only their own tickets', function () {
    $own = Ticket::factory()->count(2)->create(['requester_id' => $this->client->id]);
    $other = Ticket::factory()->create();

    Livewire::test(ListTickets::class)
        ->assertCanSeeTableRecords($own)
        ->assertCanNotSeeTableRecords([$other]);
});
