<?php

namespace App\Filament\Clusters\HelpCenter\Resources\FormResource\RelationManagers;

use App\Enums\HelpCenter\Forms\FormFieldType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
}
