<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Select::make('priority')
                            ->label(__('Priority'))
                            ->options(TicketPriority::class)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(TicketPriority::NORMAL),
                        Select::make('type')
                            ->label(__('Type'))
                            ->options(TicketType::class)
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->disabledOn(['edit'])
                    ->columnSpanFull(),
                Section::make()
                    ->schema([
                        Select::make('assignee_id')
                            ->label(__('Assignee'))
                            ->relationship(name: 'assignee', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('The agent assigned to the ticket.')),
                        Select::make('group_id')
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
                TextColumn::make('subject'),
                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::ExtraLarge),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::ExtraLarge),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
