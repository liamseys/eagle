<?php

use App\Filament\Resources\ClientResource\Pages\ListClients;
use App\Models\Client;
use App\Models\Permission;
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

it('renders the clients overview with records', function () {
    $clients = Client::factory()->count(3)->create();

    Livewire::test(ListClients::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($clients);
});

it('searches clients by name and email', function () {
    $match = Client::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    $other = Client::factory()->create(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);

    Livewire::test(ListClients::class)
        ->searchTable('Ada Lovelace')
        ->assertCanSeeTableRecords([$match])
        ->assertCanNotSeeTableRecords([$other])
        ->searchTable('grace@example.com')
        ->assertCanSeeTableRecords([$other])
        ->assertCanNotSeeTableRecords([$match]);
});

it('filters clients by active status', function () {
    $active = Client::factory()->create(['is_active' => true]);
    $inactive = Client::factory()->create(['is_active' => false]);

    Livewire::test(ListClients::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$inactive]);
});
