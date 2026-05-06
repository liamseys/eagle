<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => TicketResource::getUrl('create')),
            ])
            ->recordUrl(fn (Ticket $record): string => TicketResource::getUrl('edit', ['record' => $record]));
    }
}
