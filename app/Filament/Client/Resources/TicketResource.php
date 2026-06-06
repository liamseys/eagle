<?php

namespace App\Filament\Client\Resources;

use App\Enums\Tickets\TicketStatus;
use App\Filament\Client\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Client\Resources\TicketResource\Pages\ViewTicket;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource\RelationManagers\FieldsRelationManager;
use App\Models\Ticket;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Livewire::make(FieldsRelationManager::class, fn (Ticket $record, ViewTicket $livewire): array => [
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
                        Section::make(__('Details'))
                            ->schema([
                                Placeholder::make('priority')
                                    ->label(__('Priority'))
                                    ->content(fn (Ticket $record): ?string => $record->priority->getLabel()),

                                Placeholder::make('type')
                                    ->label(__('Type'))
                                    ->content(fn (Ticket $record): ?string => $record->type->getLabel()),

                                Placeholder::make('status')
                                    ->label(__('Status'))
                                    ->content(fn (Ticket $record): ?string => $record->status->requesterFacing()->getLabel()),
                                Placeholder::make('assignee')
                                    ->label(__('Assignee'))
                                    ->content(fn (Ticket $record): ?string => $record->assignee ? $record->assignee->name : '-'),

                                Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Ticket $record): ?string => $record->created_at?->diffForHumans()),

                                Placeholder::make('updated_at')
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
                    // Requesters never see On-hold; it is presented as Open.
                    ->formatStateUsing(fn (TicketStatus $state): string => $state->requesterFacing()->getLabel())
                    ->color(fn (TicketStatus $state): string|array|null => $state->requesterFacing()->getColor())
                    ->icon(fn (TicketStatus $state): ?string => $state->requesterFacing()->getIcon()),
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
                SelectFilter::make('status')
                    ->label(__('Status'))
                    // On-hold is internal; requesters filter by the statuses they can see.
                    ->options(collect(TicketStatus::cases())
                        ->reject(fn (TicketStatus $status): bool => $status === TicketStatus::ON_HOLD)
                        ->mapWithKeys(fn (TicketStatus $status): array => [$status->value => $status->getLabel()])
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        // Since On-hold appears as Open to requesters, the Open filter matches both.
                        if ($value === TicketStatus::OPEN->value) {
                            return $query->whereIn('status', [TicketStatus::OPEN->value, TicketStatus::ON_HOLD->value]);
                        }

                        return $query->where('status', $value);
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
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
            'view' => ViewTicket::route('/{record}'),
        ];
    }
}
