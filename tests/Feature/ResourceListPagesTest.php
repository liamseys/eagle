<?php

use App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages\ListArticles;
use App\Filament\Clusters\HelpCenter\Resources\CategoryResource\Pages\ManageCategories;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\ListForms;
use App\Filament\Clusters\Settings\Resources\GroupResource\Pages\ManageGroups;
use App\Filament\Clusters\Settings\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\ClientResource\Pages\ListClients;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Models\Group;
use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\Section;
use App\Models\Permission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

function actingAsAgentWithPermission(string $permission): User
{
    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => $permission,
        'display_name' => $permission,
        'description' => $permission,
    ]));
    $agent->load('permissions');

    test()->actingAs($agent);

    return $agent;
}

dataset('list pages with empty states', [
    'tickets' => [ListTickets::class, 'tickets', 'No tickets yet'],
    'clients' => [ListClients::class, 'clients', 'No clients yet'],
    'articles' => [ListArticles::class, 'hc-articles', 'No articles yet'],
    'forms' => [ListForms::class, 'hc-forms', 'No forms yet'],
    'categories' => [ManageCategories::class, 'hc-categories', 'No categories yet'],
    'groups' => [ManageGroups::class, 'settings', 'No groups yet'],
]);

it('shows a helpful empty state on list pages', function (string $page, string $permission, string $heading) {
    actingAsAgentWithPermission($permission);

    Livewire::test($page)
        ->assertSuccessful()
        ->assertSee($heading);
})->with('list pages with empty states');

it('shows the group description as a secondary line in the groups table', function () {
    actingAsAgentWithPermission('settings');

    Group::factory()->create([
        'name' => 'Billing team',
        'description' => 'Handles invoices and payments.',
    ]);

    Livewire::test(ManageGroups::class)
        ->assertSuccessful()
        ->assertSee('Billing team')
        ->assertSee('Handles invoices and payments.');
});

it('shows the category description as a secondary line in the categories table', function () {
    actingAsAgentWithPermission('hc-categories');

    Category::factory()->create([
        'name' => 'Getting started',
        'description' => 'Guides for new customers.',
    ]);

    Livewire::test(ManageCategories::class)
        ->assertSuccessful()
        ->assertSee('Getting started')
        ->assertSee('Guides for new customers.');
});

it('shows the section name as a secondary line in the forms table', function () {
    actingAsAgentWithPermission('hc-forms');

    $section = Section::factory()->create(['name' => 'Billing questions']);
    Form::factory()->for($section)->create(['name' => 'Refund request']);

    Livewire::test(ListForms::class)
        ->assertSuccessful()
        ->assertSee('Refund request')
        ->assertSee('Billing questions');
});

it('shows the user email as a secondary line in the users table', function () {
    $agent = actingAsAgentWithPermission('settings');

    Livewire::test(ListUsers::class)
        ->assertSuccessful()
        ->assertSee($agent->name)
        ->assertSee($agent->email);
});
