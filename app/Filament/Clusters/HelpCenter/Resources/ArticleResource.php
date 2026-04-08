<?php

namespace App\Filament\Clusters\HelpCenter\Resources;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Filament\Clusters\HelpCenter;
use App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages\CreateArticle;
use App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages\EditArticle;
use App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages\ListArticles;
use App\Models\HelpCenter\Article;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $cluster = HelpCenter::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label(__('Description'))
                                    ->autosize()
                                    ->helperText(__('(Optional) A brief description of the article. This will be displayed on the article\'s page. '))
                                    ->columnSpanFull(),
                                RichEditor::make('body')
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
                Group::make()
                    ->schema([
                        Section::make(__('Associations'))
                            ->schema([
                                Select::make('section_id')
                                    ->label(__('Section'))
                                    ->relationship('section', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Select::make('category_id')
                                            ->label(__('Category'))
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Textarea::make('description')
                                            ->maxLength(255)
                                            ->placeholder(__('(Optional) A brief description of the section.'))
                                            ->columnSpanFull(),
                                    ])
                                    ->createOptionModalHeading(__('Create section'))
                                    ->createOptionAction(
                                        fn (Action $action) => $action->modalWidth(Width::Medium),
                                    )
                                    ->required(),
                            ]),
                        Section::make(__('Status'))
                            ->schema([
                                Select::make('status')
                                    ->label(__('Status'))
                                    ->options(ArticleStatus::class)
                                    ->default(ArticleStatus::DRAFT)
                                    ->required(),
                                Toggle::make('is_public')
                                    ->label(__('Public'))
                                    ->required()
                                    ->helperText(__('Public articles are visible to everyone.')),
                            ]),
                        Section::make(__('Metadata'))
                            ->schema([
                                Placeholder::make('created_by')
                                    ->label(__('Created by'))
                                    ->content(fn (Article $record): ?string => $record->author?->name),

                                Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Article $record): ?string => $record->created_at?->diffForHumans()),

                                Placeholder::make('updated_at')
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
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->formatStateUsing(fn ($record) => new HtmlString(sprintf(
                        '%s<br><span class="text-xs text-gray-500">%s</span>',
                        $record->title,
                        $record->category?->name,
                    )))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label(__('Author'))
                    ->searchable(),
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
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
