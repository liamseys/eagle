<?php

use App\Filament\Clusters\Settings\Pages\ManageWorkflows;
use App\Models\Permission;
use App\Models\User;
use App\Settings\WorkflowSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $user = User::factory()->create();
    $user->permissions()->attach(Permission::create([
        'name' => 'settings',
        'display_name' => 'Settings',
        'description' => 'Manage application settings',
    ]));
    $user->load('permissions');

    $this->actingAs($user);
});

it('loads the workflows settings page for an authorised user', function () {
    Livewire::test(ManageWorkflows::class)->assertSuccessful();
});

it('persists business-hour targets as business minutes', function () {
    Livewire::test(ManageWorkflows::class)
        ->fillForm([
            'sla_enabled' => true,
            'sla_at_risk_threshold_percent' => 25,
            'sla_targets' => [
                'low' => ['first_response_hours' => 8, 'resolution_hours' => 48],
                'normal' => ['first_response_hours' => 5, 'resolution_hours' => 10],
                'high' => ['first_response_hours' => 2, 'resolution_hours' => 8],
                'urgent' => ['first_response_hours' => 1, 'resolution_hours' => 4],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(WorkflowSettings::class);

    expect($settings->sla_at_risk_threshold_percent)->toBe(25)
        ->and($settings->sla_targets['normal']['first_response_minutes'])->toBe(300)
        ->and($settings->sla_targets['normal']['resolution_minutes'])->toBe(600)
        ->and($settings->sla_targets['urgent']['first_response_minutes'])->toBe(60);
});
