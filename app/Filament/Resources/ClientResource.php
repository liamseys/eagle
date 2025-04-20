<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Notes;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\TicketsRelationManager;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Table;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Intl\Timezones;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

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
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('groups')
                                            ->relationship(name: 'groups', titleAttribute: 'name')
                                            ->multiple()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('description')
                                                    ->maxLength(255)
                                                    ->placeholder(__('(Optional) A brief description of the group'))
                                                    ->columnSpanFull(),
                                            ])
                                            ->createOptionModalHeading(__('Create group'))
                                            ->createOptionAction(
                                                fn (Action $action) => $action->modalWidth(MaxWidth::Large),
                                            ),
                                    ]),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->label(__('Email'))
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->label(__('Phone'))
                                            ->tel()
                                            ->maxLength(255),
                                    ]),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('locale')
                                            ->label(__('Locale'))
                                            ->options(Locales::getNames())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->default('en'),
                                        Forms\Components\Select::make('timezone')
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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Client $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
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
                Tables\Columns\ViewColumn::make('name')
                    ->label(__('Name'))
                    ->view('filament.tables.columns.avatar-name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                SpatieTagsColumn::make('tags'),
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
            TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
