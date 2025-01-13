<?php

namespace App\Filament\Resources;

use App\Enums\Forms\FormFieldType;
use App\Enums\Tickets\TicketPriority;
use App\Filament\Resources\FormResource\Pages;
use App\Models\Form;
use Filament\Forms;
use Filament\Forms\Form as FilamentForm;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(FilamentForm $form): FilamentForm
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\RichEditor::make('description')
                                    ->label(__('Description'))
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
                                    ->maxLength(500)
                                    ->live()
                                    ->helperText(fn ($state, $component) => new HtmlString(
                                        '<div class="flex flex-row justify-between gap-4">'.
                                        '<span>'.__('(Optional) A brief description of the form. This will be displayed on the form\'s page.').'</span>'.
                                        '<span class="'.((strlen($state) > $component->getMaxLength()) ? 'text-red-600' : 'text-gray-500').'">'.
                                        (strlen($state)).'/'.$component->getMaxLength().
                                        '</span>'.
                                        '</div>'
                                    )),
                            ]),
                        Forms\Components\Section::make(__('Fields'))
                            ->schema([
                                Forms\Components\Repeater::make('fields')
                                    ->label('')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label(__('Type'))
                                            ->options(FormFieldType::class)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),
                                        Forms\Components\TextInput::make('label')
                                            ->label(__('Label'))
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\KeyValue::make('options')
                                            ->addActionLabel(__('Add option'))
                                            ->requiredIf('type', [
                                                FormFieldType::CHECKBOX->value,
                                                FormFieldType::RADIO->value,
                                                FormFieldType::SELECT->value,
                                            ])
                                            ->visible(fn ($get) => in_array($get('type'), [
                                                FormFieldType::CHECKBOX->value,
                                                FormFieldType::RADIO->value,
                                                FormFieldType::SELECT->value,
                                            ]))
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('description')
                                            ->label(__('Description'))
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('is_visible')
                                            ->label(__('Visible'))
                                            ->default(true)
                                            ->required()
                                            ->helperText(__('Visible fields appear on the form, while hidden fields are only accessible to agents.')),
                                    ])
                                    ->addActionLabel(__('Add Field'))
                                    ->orderColumn('sort')
                                    ->columns(2),
                            ])
                            ->hiddenOn(['create']),
                    ])->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Associations'))
                            ->schema([
                                Forms\Components\Select::make('default_group_id')
                                    ->label(__('Default group'))
                                    ->relationship('defaultGroup', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('(Optional) When the form is submitted, the ticket will be assigned to this group.')),
                                Forms\Components\Select::make('default_ticket_priority')
                                    ->label(__('Default ticket priority'))
                                    ->options(TicketPriority::class)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText(__('The default priority assigned to tickets created from this form.')),
                            ]),
                        Forms\Components\Section::make(__('Status'))
                            ->schema([
                                Forms\Components\Toggle::make('is_public')
                                    ->label(__('Public'))
                                    ->required()
                                    ->helperText(__('Public forms are visible to everyone.')),
                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->required(),
                            ]),
                        Forms\Components\Section::make(__('Metadata'))
                            ->schema([
                                Forms\Components\Placeholder::make('created_by')
                                    ->label(__('Created by'))
                                    ->content(fn (Form $record): ?string => $record->user?->name),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Form $record): ?string => $record->created_at?->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Form $record): ?string => $record->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                        Forms\Components\Section::make(__('Embed'))
                            ->schema([
                                Forms\Components\Toggle::make('is_embeddable')
                                    ->label(__('Allow embedding'))
                                    ->default(false)
                                    ->required()
                                    ->live(),
                                Forms\Components\Textarea::make('embed_code')
                                    ->label(__('Embed code'))
                                    ->autosize()
                                    ->helperText(__('Copy and paste this code into your website to embed the form.'))
                                    ->visible(fn ($get) => $get('is_embeddable')),
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
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
            ])
            ->defaultSort('sort', 'ASC')
            ->reorderable('sort');
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
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
