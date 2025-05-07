<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('formField.type')
                    ->label(__('Type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('formField.label')
                    ->label(__('Label')),
                TextColumn::make('value')
                    ->label(__('Value'))
                    ->state(function (Model $record): string {
                        if ($record->type === 'string') {
                            return $record->value;
                        }

                        return collect(json_decode($record->value, true))
                            ->map(fn ($key) => $record->formField->options[$key])
                            ->implode(', ');
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading(__('Edit field'))
                    ->modalDescription(__('Update the value for this field. Once saved, the changes will take effect immediately.'))
                    ->modalWidth(MaxWidth::Large),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->join('hc_form_fields', 'ticket_fields.form_field_id', '=', 'hc_form_fields.id')
                    ->orderBy('hc_form_fields.sort')
                    ->select('ticket_fields.*');
            });
    }
}
