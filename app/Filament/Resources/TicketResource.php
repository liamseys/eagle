<?php

namespace App\Filament\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource\Pages\CreateTicket;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Resources\TicketResource\RelationManagers\FieldsRelationManager;
use App\Models\Ticket;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                View::make('filament.forms.components.ticket-duplicate-message')
                                    ->hidden(fn (?Ticket $record) => ! $record || ! $record->duplicate_of_ticket_id),
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
                                        Select::make('status')
                                            ->label(__('Status'))
                                            ->options(TicketStatus::class)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->hiddenOn(['create'])
                                            ->disabled(fn ($record) => $record?->status === TicketStatus::CLOSED),
                                    ])->columns(3),
                                TextInput::make('subject')
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
                Group::make()
                    ->schema([
                        View::make('filament.infolists.components.requester')
                            ->hidden(fn (?Ticket $record) => ! $record || ! $record->requester()->exists()),
                        Section::make(__('Associations'))
                            ->schema([
                                Select::make('requester_id')
                                    ->label(__('Requester'))
                                    ->relationship(name: 'requester', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The client who requested the ticket.')),
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
                        Section::make(__('Metadata'))
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Ticket $record): ?string => $record->created_at?->diffForHumans()),

                                Placeholder::make('updated_at')
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
                TextColumn::make('ticket_id')
                    ->label(__('Ticket ID'))
                    ->prefix('#')
                    ->copyable()
                    ->copyMessage(__('Ticket ID copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable(),
                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }
}
