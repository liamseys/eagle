<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Enums\HelpCenter\Forms\FormFieldType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    public function form(Schema $schema): Schema
    {
        return $schema->components(fn (?Model $record): array => match ($record->formField->type) {
            FormFieldType::TEXT => [
                TextInput::make('value')
                    ->required($record?->formField?->is_required)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ],
            FormFieldType::TEXTAREA => [
                Textarea::make('value')
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::EMAIL => [
                TextInput::make('value')
                    ->email()
                    ->required($record?->formField?->is_required)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ],
            FormFieldType::CHECKBOX => [
                CheckboxList::make('value')
                    ->options($record->formField->options)
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::RADIO => [
                Radio::make('value')
                    ->options($record->formField->options)
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::SELECT => [
                Select::make('value')
                    ->options($record->formField->options)
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::DATE => [
                DatePicker::make('value')
                    ->required($record?->formField?->is_required)
                    ->columnSpanFull(),
            ],
            FormFieldType::DATETIME_LOCAL => [
                DateTimePicker::make('value')
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
                TextColumn::make('formField.type')
                    ->label(__('Type'))
                    ->badge(),
                TextColumn::make('formField.label')
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
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('Edit field'))
                    ->modalDescription(__('Update the value for this field. Once saved, the changes will take effect immediately.'))
                    ->modalWidth(Width::Large),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
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
