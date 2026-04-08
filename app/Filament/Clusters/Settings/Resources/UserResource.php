<?php

namespace App\Filament\Clusters\Settings\Resources;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\UserResource\Pages\CreateUser;
use App\Filament\Clusters\Settings\Resources\UserResource\Pages\EditUser;
use App\Filament\Clusters\Settings\Resources\UserResource\Pages\ListUsers;
use App\Filament\Clusters\Settings\Resources\UserResource\RelationManagers\TokensRelationManager;
use App\Models\Permission;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?int $navigationSort = 5;

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $cluster = Settings::class;

    public static function form(Schema $schema): Schema
    {
        $permissions = Permission::query()
            ->orderBy('sort')
            ->get();

        return $schema
            ->components([
                Group::make()->schema([
                    Section::make()
                        ->schema([
                            Grid::make()
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Toggle::make('send_welcome_email')
                                ->label('Send welcome email')
                                ->default(true)
                                ->live()
                                ->helperText(__('By default, we\'ll send a welcome email for the user to set their password. If unchecked, you can set the password manually, and no email will be sent.'))
                                ->hiddenOn(['edit']),
                            Grid::make()
                                ->schema([
                                    TextInput::make('password')
                                        ->label(__('Password'))
                                        ->password()
                                        ->revealable()
                                        ->confirmed()
                                        ->requiredIf('send_welcome_email', false)
                                        ->maxLength(255),
                                    TextInput::make('password_confirmation')
                                        ->label(__('Confirm password'))
                                        ->password()
                                        ->revealable()
                                        ->requiredIf('send_welcome_email', false)
                                        ->maxLength(255)
                                        ->dehydrated(false),
                                ])
                                ->visible(fn ($get) => $get('send_welcome_email') === false)
                                ->hiddenOn(['edit']),
                        ]),
                    Section::make(__('Permissions'))
                        ->schema([
                            CheckboxList::make('permissions')
                                ->label('')
                                ->relationship()
                                ->options($permissions->pluck('display_name', 'id'))
                                ->descriptions($permissions->pluck('description', 'id'))
                                ->bulkToggleable()
                                ->columns(2)
                                ->disabled(fn ($record) => $record?->id === auth()->id()),
                        ]),
                    Livewire::make(TokensRelationManager::class, fn (User $record, EditUser $livewire): array => [
                        'ownerRecord' => $record,
                        'pageClass' => $livewire::class,
                    ])->hiddenOn(['create']),
                ])->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make(__('Associations'))
                            ->schema([
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
                        Section::make(__('Status'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->required()
                                    ->helperText(__('Toggle this option to enable or disable the user\'s login access to Eagle.')),
                            ]),
                        Section::make(__('Metadata'))
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (User $record): ?string => $record->created_at?->diffForHumans()),

                                Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (User $record): ?string => $record->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('groups_count')
                    ->counts('groups')
                    ->label(__('Groups')),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
