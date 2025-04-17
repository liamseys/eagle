<?php

namespace App\Filament\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Resources\TicketResource\RelationManagers\FieldsRelationManager;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                View::make('filament.forms.components.ticket-duplicate-message')
                                    ->hidden(fn (?Ticket $record) => ! $record || ! $record->duplicate_of_ticket_id),
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
                                            ->hiddenOn(['create'])
                                            ->disabled(fn ($record) => $record->status === TicketStatus::CLOSED),
                                    ])->columns(3),
                                Forms\Components\TextInput::make('subject')
                                    ->label(__('Subject'))
                                    ->placeholder(__('Enter the subject of the ticket'))
                                    ->disabledOn(['edit'])
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Livewire::make(FieldsRelationManager::class, fn (Ticket $record, EditTicket $livewire): array => [
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
                        Forms\Components\View::make('filament.infolists.components.requester')
                            ->hidden(fn (?Ticket $record) => ! $record || ! $record->requester()->exists()),
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
                Filter::make('is_assigned_to_me')
                    ->label(__('Assigned to me'))
                    ->query(fn (Builder $query): Builder => $query->where('assignee_id', auth()->id())),
                SelectFilter::make('requester')
                    ->label(__('Requester'))
                    ->relationship('requester', 'name')
                    ->searchable(),
                SelectFilter::make('priority')
                    ->label(__('Priority'))
                    ->options(TicketPriority::class)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(TicketType::class)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
