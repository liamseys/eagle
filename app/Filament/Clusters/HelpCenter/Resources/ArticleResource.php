<?php

namespace App\Filament\Clusters\HelpCenter\Resources;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Filament\Clusters\HelpCenter;
use App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages;
use App\Models\HelpCenter\Article;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = HelpCenter::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label(__('Description'))
                                    ->autosize()
                                    ->helperText(__('(Optional) A brief description of the article. This will be displayed on the article\'s page. '))
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('body')
                                    ->label(__('Content'))
                                    ->toolbarButtons([
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Associations'))
                            ->schema([
                                Forms\Components\Select::make('section_id')
                                    ->label(__('Section'))
                                    ->relationship('section', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\Select::make('category_id')
                                            ->label(__('Category'))
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('description')
                                            ->maxLength(255)
                                            ->placeholder(__('(Optional) A brief description of the section.'))
                                            ->columnSpanFull(),
                                    ])
                                    ->createOptionModalHeading(__('Create section'))
                                    ->createOptionAction(
                                        fn (Action $action) => $action->modalWidth(MaxWidth::Medium),
                                    )
                                    ->required(),
                            ]),
                        Forms\Components\Section::make(__('Status'))
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label(__('Status'))
                                    ->options(ArticleStatus::class)
                                    ->default(ArticleStatus::DRAFT)
                                    ->required(),
                                Forms\Components\Toggle::make('is_public')
                                    ->label(__('Public'))
                                    ->required()
                                    ->helperText(__('Public articles are visible to everyone.')),
                            ]),
                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('created_by')
                                    ->label(__('Created by'))
                                    ->content(fn (Article $record): ?string => $record->author?->name),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Article $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Article $record): ?string => $record->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Article::query()
                    ->with('category')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->formatStateUsing(fn ($record) => new HtmlString(sprintf(
                        '%s<br><span class="text-xs text-gray-500">%s</span>',
                        $record->title,
                        $record->category?->name,
                    )))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label(__('Author'))
                    ->searchable(),
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
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort', 'ASC');
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
