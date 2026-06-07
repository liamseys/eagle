<?php

use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Models\Client;
use App\Models\Permission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $agent = User::factory()->create(['name' => 'Casey Agent']);
    $agent->permissions()->attach(Permission::create([
        'name' => 'clients',
        'display_name' => 'Clients',
        'description' => 'Full management of clients',
    ]));
    $agent->load('permissions');

    $this->actingAs($agent);
    $this->agent = $agent;
});

it('shows internal notes attributed to their author', function () {
    $client = Client::factory()->create();
    $client->notes()->create([
        'user_id' => $this->agent->id,
        'body' => 'Spoke with the customer about billing.',
    ]);

    Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('Notes')
        ->assertSee('Spoke with the customer about billing.')
        ->assertSee('Casey Agent');
});

it('shows an empty state when a client has no notes', function () {
    $client = Client::factory()->create();

    Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('No notes yet');
});
