<?php

namespace App\Filament\Client\Resources;

use App\Enums\Tickets\TicketStatus;
use App\Filament\Client\Resources\TicketResource\Pages;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource\RelationManagers\FieldsRelationManager;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static bool $isGloballySearchable = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Livewire::make(FieldsRelationManager::class, fn (Ticket $record, Pages\ViewTicket $livewire): array => [
                            'ownerRecord' => $record,
                            'pageClass' => $livewire::class,
                        ])->hidden(function (?Ticket $record) {
                            // If no record exists, it's the create page
                            if (! $record) {
                                return true;
                            }

                            return $record->fields->isEmpty();
                        }),
                        TicketComments::make()
                            ->hiddenOn(['create']),
                    ])->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Details'))
                            ->schema([
                                Forms\Components\Placeholder::make('priority')
                                    ->label(__('Priority'))
                                    ->content(fn (Ticket $record): ?string => $record->priority->getLabel()),

                                Forms\Components\Placeholder::make('type')
                                    ->label(__('Type'))
                                    ->content(fn (Ticket $record): ?string => $record->type->getLabel()),

                                Forms\Components\Placeholder::make('status')
                                    ->label(__('Status'))
                                    ->content(fn (Ticket $record): ?string => $record->status->getLabel()),
                                Forms\Components\Placeholder::make('assignee')
                                    ->label(__('Assignee'))
                                    ->content(fn (Ticket $record): ?string => $record->assignee ? $record->assignee->name : '-'),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Ticket $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Ticket $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                            ->columns(1)
                            ->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label(__('Ticket ID'))
                    ->prefix('#')
                    ->copyable()
                    ->copyMessage(__('Ticket ID copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->searchable(),
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
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(TicketStatus::class)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('ticket_id', 'DESC');
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
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }
}
