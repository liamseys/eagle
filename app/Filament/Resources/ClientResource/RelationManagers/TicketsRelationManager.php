<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Models\Ticket;
use App\Support\RichEditor\CannedResponsesPlugin;
use App\Support\TicketMergeTags;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
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
                RichEditor::make('comment')
                    ->label(__('Comment'))
                    ->plugins([CannedResponsesPlugin::make()])
                    ->mergeTags(TicketMergeTags::labels())
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['bulletList', 'orderedList'],
                        ['cannedResponses', 'mergeTags'],
                        ['undo', 'redo'],
                    ])
                    ->maxLength(2500)
                    ->required()
                    ->columnSpanFull()
                    ->visibleOn('create'),
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
                    ])
                    ->columns(2),
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
                    ->modalWidth(Width::ExtraLarge)
                    ->after(function (Ticket $record, array $data) {
                        $user = auth()->user();

                        $record->comments()->create([
                            'authorable_type' => $user::class,
                            'authorable_id' => $user->id,
                            'body' => RichContentRenderer::make($data['comment'])
                                ->mergeTags(TicketMergeTags::valuesFor($record))
                                ->toHtml(),
                            'is_public' => true,
                        ]);
                    }),
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
