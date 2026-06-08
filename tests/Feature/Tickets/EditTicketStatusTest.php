<?php

use App\Enums\Tickets\TicketStatus;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
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
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => 'tickets',
        'display_name' => 'Tickets',
        'description' => 'Full management of tickets',
    ]));
    $agent->load('permissions');

    $this->actingAs($agent);
});

it('saves a ticket that is still in the New status', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::NEW)->create();

    Livewire::test(EditTicket::class, ['record' => $ticket->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($ticket->fresh()->status)->toBe(TicketStatus::NEW);
});
