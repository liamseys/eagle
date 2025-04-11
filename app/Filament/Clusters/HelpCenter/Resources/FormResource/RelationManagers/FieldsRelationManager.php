<?php

namespace App\Filament\Clusters\HelpCenter\Resources\FormResource\RelationManagers;

use App\Enums\HelpCenter\Forms\FormFieldType;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make(__('General'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label(__('Type'))
                                            ->options(FormFieldType::class)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),
                                        Forms\Components\TextInput::make('label')
                                            ->label(__('Label'))
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('description')
                                            ->label(__('Description'))
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('is_visible')
                                            ->label(__('Visible'))
                                            ->default(true)
                                            ->required()
                                            ->helperText(__('Visible fields appear on the form, while hidden fields are only accessible to agents.')),
                                    ]),
                            ]),
                        Tabs\Tab::make(__('Options'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Forms\Components\KeyValue::make('options')
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
                        Tabs\Tab::make(__('Extra validation'))
                            ->icon('heroicon-o-variable')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Toggle::make('is_required')
                                            ->label(__('Required'))
                                            ->default(true)
                                            ->required()
                                            ->helperText(__('Indicates if this field is required and must be filled out before the form can be submitted.')),
                                    ]),
                                Forms\Components\Repeater::make('validation_rules')
                                    ->addActionLabel(__('Add rule'))
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\Select::make('rule')
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

                                                Forms\Components\TextInput::make('value')
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
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('label'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('New field'))
                    ->modalHeading(__('Create field')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->recordTitle('field'),
                Tables\Actions\DeleteAction::make()
                    ->recordTitle('field'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort', 'ASC')
            ->reorderable('sort');
    }
}
