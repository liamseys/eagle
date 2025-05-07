<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Enums\HelpCenter\Forms\FormFieldType;
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
        return $form->schema(fn (?Model $record): array => match ($record->formField->type) {
            FormFieldType::TEXT => [
                Forms\Components\TextInput::make('value')
                    ->required($record?->formField?->is_required)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ],
            FormFieldType::TEXTAREA => [
                Forms\Components\Textarea::make('value')
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::EMAIL => [
                Forms\Components\TextInput::make('value')
                    ->email()
                    ->required($record?->formField?->is_required)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ],
            FormFieldType::CHECKBOX => [
                Forms\Components\CheckboxList::make('value')
                    ->options($record->formField->options)
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::RADIO => [
                Forms\Components\Radio::make('value')
                    ->options($record->formField->options)
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::SELECT => [
                Forms\Components\Select::make('value')
                    ->options($record->formField->options)
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::DATE => [
                Forms\Components\DatePicker::make('value')
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::DATETIME_LOCAL => [
                Forms\Components\DateTimePicker::make('value')
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            default => [],
        });
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
                    ->state(fn (Model $record): string => match ($record->formField->type) {
                        FormFieldType::CHECKBOX => collect($record->value)
                            ->map(fn ($key) => $record->formField->options[$key] ?? $key)
                            ->implode(', '),

                        FormFieldType::RADIO, FormFieldType::SELECT => $record->formField->options[$record->value] ?? $record->value,

                        default => $record->value,
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
