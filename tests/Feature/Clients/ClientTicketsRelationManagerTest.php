<?php

use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Filament\Resources\ClientResource\RelationManagers\TicketsRelationManager;
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
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => 'clients',
        'display_name' => 'Clients',
        'description' => 'Full management of clients',
    ]));
    $agent->load('permissions');

    $this->actingAs($agent);
});

it('renders the client tickets relation manager with the redesigned columns', function () {
    $client = Client::factory()->create();
    $tickets = Ticket::factory()->count(3)->create(['requester_id' => $client->id]);

    Livewire::test(TicketsRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => EditClient::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($tickets)
        ->assertCanRenderTableColumn('subject')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('priority')
        ->assertCanRenderTableColumn('sla_status');
});

it('only lists tickets belonging to the client', function () {
    $client = Client::factory()->create();
    $own = Ticket::factory()->create(['requester_id' => $client->id]);
    $other = Ticket::factory()->create();

    Livewire::test(TicketsRelationManager::class, [
        'ownerRecord' => $client,
        'pageClass' => EditClient::class,
    ])
        ->assertCanSeeTableRecords([$own])
        ->assertCanNotSeeTableRecords([$other]);
});
