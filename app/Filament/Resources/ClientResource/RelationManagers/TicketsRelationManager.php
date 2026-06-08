<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['duplicateOf']))
            ->recordUrl(fn (Ticket $record): string => TicketResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->weight(FontWeight::SemiBold)
                    ->limit(60)
                    ->tooltip(fn (Ticket $record): ?string => mb_strlen($record->subject) > 60 ? $record->subject : null)
                    // Fold the identifier and timing into a quiet secondary line (with an inline
                    // copy-to-clipboard control for the ticket ID) so the subject can lead and the
                    // row stays scannable. The requester is omitted: it is always this client.
                    ->description(fn (Ticket $record): HtmlString => new HtmlString(
                        view('filament.tables.ticket-meta', ['record' => $record, 'showRequester' => false])->render()
                    ))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query->where('subject', 'like', "%{$search}%")
                                ->orWhere('ticket_id', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge(),
                TextColumn::make('sla_status')
                    ->label(__('SLA'))
                    ->badge()
                    ->state(fn (Ticket $record): ?string => $record->sla()->overallState()?->getLabel())
                    ->color(fn (Ticket $record): string => $record->sla()->overallState()?->getColor() ?? 'gray')
                    ->icon(fn (Ticket $record): ?string => $record->sla()->overallState()?->getIcon())
                    ->placeholder('—')
                    ->toggleable(),
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
                Filter::make('is_assigned_to_me')
                    ->label(__('Assigned to me'))
                    ->query(fn (Builder $query): Builder => $query->where('assignee_id', auth()->id())),
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
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => TicketResource::getUrl('create')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('ticket_id', 'DESC');
    }
}
