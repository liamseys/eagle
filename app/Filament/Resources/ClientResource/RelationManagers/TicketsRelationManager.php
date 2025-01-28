<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('priority')
                            ->label(__('Priority'))
                            ->options(TicketPriority::class)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(TicketPriority::NORMAL),
                        Forms\Components\Select::make('type')
                            ->label(__('Type'))
                            ->options(TicketType::class)
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->disabledOn(['edit'])
                    ->columnSpanFull(),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('assignee_id')
                            ->label(__('Assignee'))
                            ->relationship(name: 'assignee', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('The agent assigned to the ticket.')),
                        Forms\Components\Select::make('group_id')
                            ->label(__('Group'))
                            ->relationship(name: 'group', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('The group assigned to the ticket.')),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                Tables\Columns\TextColumn::make('subject'),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth(MaxWidth::ExtraLarge),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::ExtraLarge),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
