<?php

namespace App\Filament\Client\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Client\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Client\Resources\TicketResource\Pages\ViewTicket;
use App\Filament\Forms\Components\TicketComments;
use App\Filament\Resources\TicketResource\RelationManagers\FieldsRelationManager;
use App\Models\Ticket;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

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
            ->recordUrl(fn (Ticket $record): string => static::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->weight(FontWeight::SemiBold)
                    ->limit(60)
                    ->tooltip(fn (Ticket $record): ?string => mb_strlen($record->subject) > 60 ? $record->subject : null)
                    // Fold the identifier (with an inline copy control) and timing into a quiet
                    // secondary line so the subject can lead and the row stays scannable. The
                    // requester and duplicate markers are agent-only and omitted in the portal.
                    ->description(fn (Ticket $record): HtmlString => new HtmlString(
                        view('filament.tables.ticket-meta', ['record' => $record, 'showRequester' => false, 'showDuplicate' => false])->render()
                    ))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query->where('subject', 'like', "%{$search}%")
                                ->orWhere('ticket_id', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    // Requesters never see On-hold; it is presented as Open.
                    ->formatStateUsing(fn (TicketStatus $state): string => $state->requesterFacing()->getLabel())
                    ->color(fn (TicketStatus $state): string|array|null => $state->requesterFacing()->getColor())
                    ->icon(fn (TicketStatus $state): ?string => $state->requesterFacing()->getIcon()),
                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
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
