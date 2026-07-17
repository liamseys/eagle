<?php

namespace App\Filament\Clusters\HelpCenter\Resources\FormResource\RelationManagers;

use App\Actions\Forms\GenerateFormFields;
use App\Enums\HelpCenter\Forms\FormFieldType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make(__('General'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('type')
                                            ->label(__('Type'))
                                            ->options(FormFieldType::class)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),
                                        TextInput::make('label')
                                            ->label(__('Label'))
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('description')
                                            ->label(__('Description'))
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Toggle::make('is_visible')
                                            ->label(__('Visible'))
                                            ->default(true)
                                            ->required()
                                            ->helperText(__('Visible fields appear on the form, while hidden fields are only accessible to agents.')),
                                    ]),
                            ]),
                        Tab::make(__('Options'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                KeyValue::make('options')
                                    ->addActionLabel(__('Add option'))
                                    ->requiredIf('type', [
                                        FormFieldType::CHECKBOX->value,
                                        FormFieldType::RADIO->value,
                                        FormFieldType::SELECT->value,
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn ($get) => in_array($get('type'), [
                                FormFieldType::CHECKBOX->value,
                                FormFieldType::RADIO->value,
                                FormFieldType::SELECT->value,
                            ])),
                        Tab::make(__('Extra validation'))
                            ->icon('heroicon-o-variable')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Toggle::make('is_required')
                                            ->label(__('Required'))
                                            ->default(true)
                                            ->required()
                                            ->helperText(__('Indicates if this field is required and must be filled out before the form can be submitted.')),
                                    ]),
                                Repeater::make('validation_rules')
                                    ->addActionLabel(__('Add rule'))
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Select::make('rule')
                                                    ->label(__('Rule'))
                                                    ->options([
                                                        'string' => 'String',
                                                        'max' => 'Maximum length',
                                                        'min' => 'Minimum length',
                                                        'email' => 'Email address',
                                                        'integer' => 'Integer',
                                                        'boolean' => 'Boolean',
                                                        'url' => 'Valid URL',
                                                        'in' => 'In list (comma-separated)',
                                                        'regex' => 'Regex pattern',
                                                    ])
                                                    ->live()
                                                    ->required(),

                                                TextInput::make('value')
                                                    ->label(__('Value'))
                                                    ->maxLength(255)
                                                    ->disabled(fn ($get) => ! in_array($get('rule'), ['max', 'min', 'in', 'regex']))
                                                    ->required(fn ($get) => in_array($get('rule'), ['max', 'min', 'in', 'regex'])),
                                            ]),
                                    ])
                                    ->defaultItems(0),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('label'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                $this->getGenerateWithAiAction(),
                CreateAction::make()
                    ->label(__('New field'))
                    ->modalHeading(__('Create field')),
            ])
            ->recordActions([
                EditAction::make()
                    ->recordTitle('field'),
                DeleteAction::make()
                    ->recordTitle('field'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort', 'ASC')
            ->reorderable('sort');
    }

    /**
     * The "Generate with AI" header action: describe the form in natural language,
     * preview the AI-suggested fields, and only create them once accepted.
     */
    protected function getGenerateWithAiAction(): Action
    {
        return Action::make('generateWithAi')
            ->label(__('Generate with AI'))
            ->icon('heroicon-m-sparkles')
            ->color('gray')
            ->modalHeading(__('Generate fields with AI'))
            ->modalDescription(__('Describe the form you need and review the suggested fields. Nothing is added to the form until you accept the preview.'))
            ->modalSubmitActionLabel(__('Add fields'))
            ->modalWidth(Width::TwoExtraLarge)
            ->schema([
                Textarea::make('prompt')
                    ->label(__('Description'))
                    ->placeholder(__('e.g. Create a customer onboarding form with first name, last name, email, phone number, company, job title, and a required checkbox to accept the privacy policy.'))
                    ->rows(4)
                    ->required()
                    ->belowContent(
                        Action::make('generate')
                            ->label(fn (Get $schemaGet): string => filled($schemaGet('generated_fields'))
                                ? __('Generate again')
                                : __('Generate'))
                            ->icon('heroicon-m-sparkles')
                            ->action(function (Get $schemaGet, Set $schemaSet, GenerateFormFields $generateFormFields): void {
                                $description = trim((string) $schemaGet('prompt'));

                                if ($description === '') {
                                    Notification::make()
                                        ->warning()
                                        ->title(__('Describe your form first'))
                                        ->body(__('Enter a description of the fields you want before generating.'))
                                        ->send();

                                    return;
                                }

                                try {
                                    $fields = $generateFormFields->handle($description);
                                } catch (Throwable $exception) {
                                    report($exception);

                                    Notification::make()
                                        ->danger()
                                        ->title(__('Generation failed'))
                                        ->body(__('The fields could not be generated. Please try again.'))
                                        ->send();

                                    return;
                                }

                                if ($fields === []) {
                                    Notification::make()
                                        ->warning()
                                        ->title(__('No fields generated'))
                                        ->body(__('Try describing the fields you want in more detail.'))
                                        ->send();

                                    return;
                                }

                                $schemaSet('generated_fields', $fields);
                            })
                    ),
                Hidden::make('generated_fields')
                    ->default([]),
                Placeholder::make('preview')
                    ->label(__('Preview'))
                    ->content(fn (Get $get) => view('filament.forms.components.generated-fields-preview', [
                        'fields' => $get('generated_fields') ?? [],
                    ]))
                    ->visible(fn (Get $get): bool => filled($get('generated_fields'))),
            ])
            ->action(function (array $data, Action $action): void {
                $fields = $data['generated_fields'] ?? [];

                if (blank($fields)) {
                    Notification::make()
                        ->warning()
                        ->title(__('Nothing to add yet'))
                        ->body(__('Generate a preview of the fields before adding them to the form.'))
                        ->send();

                    $action->halt();
                }

                $form = $this->getOwnerRecord();

                foreach ($fields as $field) {
                    $form->fields()->create($field);
                }

                Notification::make()
                    ->success()
                    ->title(__('Fields added'))
                    ->body(trans_choice('{1} :count field has been added to the form.|[2,*] :count fields have been added to the form.', count($fields)))
                    ->send();
            });
    }
}
