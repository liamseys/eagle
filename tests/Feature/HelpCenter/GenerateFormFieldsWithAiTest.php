<?php

use App\Ai\Agents\FormFieldGenerator;
use App\Enums\HelpCenter\Forms\FormFieldType;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\EditForm;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\RelationManagers\FieldsRelationManager;
use App\Models\HelpCenter\Form;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $this->actingAs(User::factory()->create());
});

function fieldsRelationManager(Form $form): Testable
{
    return Livewire::test(FieldsRelationManager::class, [
        'ownerRecord' => $form,
        'pageClass' => EditForm::class,
    ]);
}

it('has a generate with ai header action', function () {
    fieldsRelationManager(Form::factory()->create())
        ->assertActionExists(TestAction::make('generateWithAi')->table())
        ->assertActionVisible(TestAction::make('generateWithAi')->table());
});

it('generates a preview and only creates the fields once accepted', function () {
    FormFieldGenerator::fake([[
        'fields' => [
            ['type' => 'text', 'label' => 'First name', 'description' => '', 'is_required' => true, 'options' => []],
            ['type' => 'email', 'label' => 'Email', 'description' => 'We only use this to contact you.', 'is_required' => true, 'options' => []],
            ['type' => 'checkbox', 'label' => 'I accept the privacy policy', 'description' => '', 'is_required' => true, 'options' => ['I accept the privacy policy']],
        ],
    ]]);

    $form = Form::factory()->create();

    $component = fieldsRelationManager($form)
        ->mountAction(TestAction::make('generateWithAi')->table())
        ->setActionData(['prompt' => 'A customer onboarding form with a privacy policy checkbox'])
        ->assertActionExists(
            TestAction::make('generate')->schemaComponent('prompt'),
            checkActionUsing: fn ($action): bool => $action->getLabel() === __('Generate'),
        )
        ->callAction(TestAction::make('generate')->schemaComponent('prompt'))
        ->assertActionExists(
            TestAction::make('generate')->schemaComponent('prompt'),
            checkActionUsing: fn ($action): bool => $action->getLabel() === __('Generate again'),
        )
        ->assertActionDataSet(function (array $data): bool {
            expect($data['generated_fields'])->toHaveCount(3)
                ->and(collect($data['generated_fields'])->pluck('label')->all())
                ->toBe(['First name', 'Email', 'I accept the privacy policy']);

            return true;
        });

    // The preview exists, but nothing is created until the user accepts it.
    expect($form->fields()->count())->toBe(0);

    $component
        ->callMountedAction()
        ->assertNotified(__('Fields added'));

    FormFieldGenerator::assertPrompted('A customer onboarding form with a privacy policy checkbox');

    $fields = $form->fields()->orderBy('sort')->get();

    expect($fields)->toHaveCount(3)
        ->and($fields[0]->type)->toBe(FormFieldType::TEXT)
        ->and($fields[0]->label)->toBe('First name')
        ->and($fields[0]->is_required)->toBeTruthy()
        ->and($fields[0]->is_visible)->toBeTrue()
        ->and($fields[1]->type)->toBe(FormFieldType::EMAIL)
        ->and($fields[1]->description)->toBe('We only use this to contact you.')
        ->and($fields[2]->type)->toBe(FormFieldType::CHECKBOX)
        ->and($fields[2]->options)->toBe(['i_accept_the_privacy_policy' => 'I accept the privacy policy']);
});

it('renders the generated fields preview with labels, types, and options', function () {
    $html = view('filament.forms.components.generated-fields-preview', [
        'fields' => [
            ['type' => 'text', 'label' => 'First name', 'description' => null, 'is_required' => true, 'is_visible' => true, 'options' => null],
            ['type' => 'select', 'label' => 'Country', 'description' => 'Where your company is based.', 'is_required' => false, 'is_visible' => true, 'options' => ['belgium' => 'Belgium']],
        ],
    ])->render();

    expect($html)
        ->toContain('2 fields will be added to the form.')
        ->toContain('First name')
        ->toContain('Country')
        ->toContain('Where your company is based.')
        ->toContain('Belgium')
        ->toContain('Text')
        ->toContain('Select');
});

it('does not create fields when the modal is cancelled after generating', function () {
    FormFieldGenerator::fake([[
        'fields' => [
            ['type' => 'text', 'label' => 'First name', 'description' => '', 'is_required' => true, 'options' => []],
        ],
    ]]);

    $form = Form::factory()->create();

    fieldsRelationManager($form)
        ->mountAction(TestAction::make('generateWithAi')->table())
        ->setActionData(['prompt' => 'A customer onboarding form'])
        ->callAction(TestAction::make('generate')->schemaComponent('prompt'))
        ->unmountAction();

    FormFieldGenerator::assertPrompted('A customer onboarding form');

    expect($form->fields()->count())->toBe(0);
});

it('halts when accepting before anything has been generated', function () {
    $form = Form::factory()->create();

    fieldsRelationManager($form)
        ->callAction(TestAction::make('generateWithAi')->table(), data: [
            'prompt' => 'A customer onboarding form',
        ])
        ->assertNotified(__('Nothing to add yet'));

    expect($form->fields()->count())->toBe(0);
});

it('warns when generating without a description and never prompts the agent', function () {
    FormFieldGenerator::fake();

    fieldsRelationManager(Form::factory()->create())
        ->mountAction(TestAction::make('generateWithAi')->table())
        ->callAction(TestAction::make('generate')->schemaComponent('prompt'))
        ->assertNotified(__('Describe your form first'));

    FormFieldGenerator::assertNeverPrompted();
});

it('shows an error notification when generation fails', function () {
    FormFieldGenerator::fake(function () {
        throw new RuntimeException('Provider unavailable');
    });

    $form = Form::factory()->create();

    fieldsRelationManager($form)
        ->mountAction(TestAction::make('generateWithAi')->table())
        ->setActionData(['prompt' => 'A customer onboarding form'])
        ->callAction(TestAction::make('generate')->schemaComponent('prompt'))
        ->assertNotified(__('Generation failed'));

    expect($form->fields()->count())->toBe(0);
});
