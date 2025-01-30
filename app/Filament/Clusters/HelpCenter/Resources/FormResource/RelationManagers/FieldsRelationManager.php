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
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make()
                                            ->schema([
                                                Forms\Components\Toggle::make('is_required')
                                                    ->label(__('Required'))
                                                    ->default(true)
                                                    ->required()
                                                    ->helperText(__('Indicates if this field is required and must be filled out before the form can be submitted.')),
                                                Forms\Components\Toggle::make('is_visible')
                                                    ->label(__('Visible'))
                                                    ->default(true)
                                                    ->required()
                                                    ->helperText(__('Visible fields appear on the form, while hidden fields are only accessible to agents.')),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make(__('Options'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                // ...
                            ]),
                        Tabs\Tab::make(__('Extra validation'))
                            ->icon('heroicon-o-variable')
                            ->schema([
                                // ...
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
