<?php

namespace App\Filament\Clusters\Settings\Resources;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\GroupResource\Pages\ManageGroups;
use App\Models\Group;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?int $navigationSort = 4;

    protected static ?string $model = Group::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Settings::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('description')
                    ->label(__('Description'))
                    ->maxLength(255)
                    ->placeholder(__('(Optional) A brief description of the group'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->weight(FontWeight::Medium)
                    ->description(fn (Group $record): ?string => $record->description)
                    ->searchable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Users'))
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-users'),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading(__('No groups yet'))
            ->emptyStateDescription(__('Groups let you route tickets, forms, and clients to the right team.'))
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Large),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGroups::route('/'),
        ];
    }
}
