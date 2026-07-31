<?php

use App\Filament\Clusters\Settings\Pages\ManageAdvanced;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\Section;
use App\Models\Permission;
use App\Models\User;
use App\Settings\AdvancedSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('serves help center pages without noindex directives by default', function () {
    $this->get(route('index', ['locale' => 'en']))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag')
        ->assertDontSee('<meta name="robots"', false);
});

it('serves noindex directives on help center pages when indexing is disabled', function () {
    AdvancedSettings::fake(['hc_search_engine_indexing' => false]);

    $this->get(route('index', ['locale' => 'en']))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex')
        ->assertSee('<meta name="robots" content="noindex">', false);
});

it('serves noindex directives on embedded forms when indexing is disabled', function () {
    AdvancedSettings::fake(['hc_search_engine_indexing' => false]);

    $form = Form::factory()->for(Section::factory())->create([
        'is_public' => true,
        'is_active' => true,
        'is_embeddable' => true,
    ]);

    $this->get(route('forms.embed', ['locale' => 'en', 'form' => $form]))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex')
        ->assertSee('<meta name="robots" content="noindex">', false);
});

it('does not add noindex directives outside the help center', function () {
    AdvancedSettings::fake(['hc_search_engine_indexing' => false]);

    $this->get(route('filament.client.auth.login'))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag');
});

it('lets administrators toggle help center indexing from the advanced settings page', function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $user = User::factory()->create(['is_active' => true]);
    $user->permissions()->attach(Permission::create([
        'name' => 'settings',
        'display_name' => 'Settings',
        'description' => 'Manage application settings',
    ]));
    $user->load('permissions');

    $this->actingAs($user);

    Livewire::test(ManageAdvanced::class)
        ->fillForm(['hc_search_engine_indexing' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(AdvancedSettings::class)->refresh()->hc_search_engine_indexing)->toBeFalse();
});
