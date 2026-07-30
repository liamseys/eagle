<?php

use App\Filament\Client\Widgets\CommonIssues;
use App\Models\Client;
use App\Models\Group;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\Section;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function portalForm(Section $section, bool $isPublic, bool $featured, string $name, bool $isActive = true): Form
{
    return Form::factory()->for($section)->create([
        'name' => $name,
        'is_public' => $isPublic,
        'is_active' => $isActive,
        'settings' => ['client_portal_featured' => $featured],
    ]);
}

it('shows only forms enabled for the client portal', function () {
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $section = Section::factory()->create();
    portalForm($section, isPublic: true, featured: true, name: 'Visible Featured Form');
    portalForm($section, isPublic: true, featured: false, name: 'Public But Not Featured');
    portalForm($section, isPublic: false, featured: true, name: 'Featured But Private');

    Livewire::test(CommonIssues::class)
        ->assertSuccessful()
        ->assertSee('Visible Featured Form')
        ->assertDontSee('Public But Not Featured')
        ->assertDontSee('Featured But Private');
});

it('hides categories that contain no portal-enabled forms', function () {
    $featuredSection = Section::factory()->create(['name' => 'Featured Category']);
    portalForm($featuredSection, isPublic: true, featured: true, name: 'Visible Form');

    $emptySection = Section::factory()->create(['name' => 'Empty Category']);
    portalForm($emptySection, isPublic: true, featured: false, name: 'Not Featured Form');

    $sections = (new CommonIssues)->getViewData()['sections'];

    expect($sections->pluck('name'))
        ->toContain('Featured Category')
        ->not->toContain('Empty Category');

    // Within a visible category, only the featured forms are loaded for display.
    expect($sections->firstWhere('name', 'Featured Category')->forms)->toHaveCount(1);
});

it('hides deactivated forms from the client portal', function () {
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $section = Section::factory()->create();
    portalForm($section, isPublic: true, featured: true, name: 'Active Featured Form');
    portalForm($section, isPublic: true, featured: true, name: 'Deactivated Featured Form', isActive: false);

    Livewire::test(CommonIssues::class)
        ->assertSuccessful()
        ->assertSee('Active Featured Form')
        ->assertDontSee('Deactivated Featured Form');
});

it('hides group-restricted forms from clients outside the allowed groups in the portal', function () {
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $section = Section::factory()->create();
    portalForm($section, isPublic: true, featured: true, name: 'Unrestricted Featured Form');
    $restrictedForm = portalForm($section, isPublic: true, featured: true, name: 'Restricted Featured Form');
    $restrictedForm->groups()->attach(Group::factory()->create());

    $this->actingAs(Client::factory()->create(), 'client');

    Livewire::test(CommonIssues::class)
        ->assertSuccessful()
        ->assertSee('Unrestricted Featured Form')
        ->assertDontSee('Restricted Featured Form');
});

it('shows group-restricted forms to clients in an allowed group in the portal', function () {
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $section = Section::factory()->create();
    $restrictedForm = portalForm($section, isPublic: true, featured: true, name: 'Restricted Featured Form');

    $group = Group::factory()->create();
    $restrictedForm->groups()->attach($group);

    $client = Client::factory()->create();
    $client->groups()->attach($group);

    $this->actingAs($client, 'client');

    Livewire::test(CommonIssues::class)
        ->assertSuccessful()
        ->assertSee('Restricted Featured Form');
});
