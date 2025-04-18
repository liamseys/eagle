<?php

namespace App\Filament\Clusters\Settings\Resources;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\UserResource\Pages;
use App\Filament\Clusters\Settings\Resources\UserResource\Pages\EditUser;
use App\Filament\Clusters\Settings\Resources\UserResource\RelationManagers\TokensRelationManager;
use App\Models\Permission;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?int $navigationSort = 5;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $cluster = Settings::class;

    public static function form(Form $form): Form
    {
        $permissions = Permission::query()
            ->orderBy('sort')
            ->get();

        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Forms\Components\Toggle::make('send_welcome_email')
                                ->label('Send welcome email')
                                ->default(true)
                                ->live()
                                ->helperText(__('By default, we\'ll send a welcome email for the user to set their password. If unchecked, you can set the password manually, and no email will be sent.'))
                                ->hiddenOn(['edit']),
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('password')
                                        ->label(__('Password'))
                                        ->password()
                                        ->revealable()
                                        ->confirmed()
                                        ->requiredIf('send_welcome_email', false)
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('password_confirmation')
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
                    Forms\Components\Section::make(__('Permissions'))
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Associations'))
                            ->schema([
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
                        Forms\Components\Section::make(__('Status'))
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->required()
                                    ->helperText(__('Toggle this option to enable or disable the user\'s login access to Eagle.')),
                            ]),
                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (User $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('groups_count')
                    ->counts('groups')
                    ->label(__('Groups')),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
