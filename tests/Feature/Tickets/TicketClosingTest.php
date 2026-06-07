<?php

use App\Enums\Tickets\TicketStatus;
use App\Filament\Client\Resources\TicketResource\Pages\ViewTicket;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

it('offers agents no manual close action on a ticket', function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => 'tickets',
        'display_name' => 'Tickets',
        'description' => 'Full management of tickets',
    ]));
    $agent->load('permissions');
    $this->actingAs($agent);

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    Livewire::test(EditTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertSuccessful()
        ->assertDontSee(__('Close now'))
        ->assertDontSee(__('Schedule for closing'));
});

it('offers requesters no close action on their ticket', function () {
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $client = Client::factory()->create();
    $this->actingAs($client, 'client');

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'requester_id' => $client->id,
    ]);

    Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertSuccessful()
        ->assertDontSee(__('Close ticket'));
});
