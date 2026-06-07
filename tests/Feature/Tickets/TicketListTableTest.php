<?php

use App\Filament\Resources\TicketResource\Pages\ListTickets;
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

it('renders the ticket overview with records', function () {
    $tickets = Ticket::factory()->count(3)->create();

    Livewire::test(ListTickets::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($tickets);
});

it('renders rows even when a ticket has no requester', function () {
    $ticket = Ticket::factory()->create(['requester_id' => null]);

    Livewire::test(ListTickets::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$ticket]);
});

it('searches tickets by subject and by ticket id', function () {
    $match = Ticket::factory()->create(['subject' => 'Printer on fire']);
    $other = Ticket::factory()->create(['subject' => 'Coffee machine broken']);

    Livewire::test(ListTickets::class)
        ->searchTable('Printer on fire')
        ->assertCanSeeTableRecords([$match])
        ->assertCanNotSeeTableRecords([$other])
        ->searchTable((string) $other->ticket_id)
        ->assertCanSeeTableRecords([$other])
        ->assertCanNotSeeTableRecords([$match]);
});
