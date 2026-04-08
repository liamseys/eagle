<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Notes;
use App\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Filament\Resources\ClientResource\Pages\ListClients;
use App\Filament\Resources\ClientResource\RelationManagers\TicketsRelationManager;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Intl\Timezones;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('groups')
                                            ->relationship(name: 'groups', titleAttribute: 'name')
                                            ->multiple()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),
                                                TextInput::make('description')
                                                    ->maxLength(255)
                                                    ->placeholder(__('(Optional) A brief description of the group'))
                                                    ->columnSpanFull(),
                                            ])
                                            ->createOptionModalHeading(__('Create group'))
                                            ->createOptionAction(
                                                fn (Action $action) => $action->modalWidth(Width::Large),
                                            ),
                                    ]),
                                Grid::make()
                                    ->schema([
                                        TextInput::make('email')
                                            ->label(__('Email'))
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->label(__('Phone'))
                                            ->tel()
                                            ->maxLength(255),
                                    ]),
                                Grid::make()
                                    ->schema([
                                        Select::make('locale')
                                            ->label(__('Locale'))
                                            ->options(Locales::getNames())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default('en'),
                                        Select::make('timezone')
                                            ->label(__('Timezone'))
                                            ->options(Timezones::getNames())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default('UTC'),
                                    ]),
                                SpatieTagsInput::make('tags'),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make(__('Metadata'))
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Client $record): ?string => $record->created_at?->diffForHumans()),

                                Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Client $record): ?string => $record->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                        Notes::make()
                            ->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('name')
                    ->label(__('Name'))
                    ->view('filament.tables.columns.avatar-name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                SpatieTagsColumn::make('tags'),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
