<?php

namespace App\Filament\Clusters\HelpCenter\Resources;

use App\Filament\Clusters\HelpCenter;
use App\Filament\Clusters\HelpCenter\Resources\CategoryResource\Pages\ManageCategories;
use App\Models\HelpCenter\Category;
use App\Services\Icons;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = HelpCenter::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('icon')
                    ->label(__('Icon'))
                    ->options(Icons::all())
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->maxLength(255)
                    ->placeholder(__('(Optional) A brief description of the category'))
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
                    ->description(fn (Category $record): ?string => filled($record->description)
                        ? Str::limit($record->description, 75)
                        : null)
                    ->searchable(),
                TextColumn::make('articles_count')
                    ->counts('articles')
                    ->label(__('Articles'))
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-document-text'),
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
            ->emptyStateHeading(__('No categories yet'))
            ->emptyStateDescription(__('Categories group your Help Center sections and articles so clients can browse them easily.'))
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort', 'ASC')
            ->reorderable('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
