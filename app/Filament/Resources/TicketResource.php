<?php

namespace App\Filament\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
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
                                        Forms\Components\Select::make('status')
                                            ->label(__('Status'))
                                            ->options(TicketStatus::class)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default(TicketStatus::OPEN),
                                    ])->columns(3),
                                Forms\Components\TextInput::make('subject')
                                    ->label(__('Subject'))
                                    ->placeholder(__('Enter the subject of the ticket'))
                                    ->disabled()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Associations'))
                            ->schema([
                                Forms\Components\Select::make('requester_id')
                                    ->label(__('Requester'))
                                    ->relationship(name: 'requester', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The client who requested the ticket.')),
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
                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Ticket $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Ticket $record): ?string => $record->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
