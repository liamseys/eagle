<?php

namespace App\Filament\Clusters\HelpCenter\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Filament\Clusters\HelpCenter;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\EditForm;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\RelationManagers\FieldsRelationManager;
use App\Models\HelpCenter\Form;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form as FilamentForm;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $cluster = HelpCenter::class;

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
                        Livewire::make(FieldsRelationManager::class, fn (Form $record, EditForm $livewire): array => [
                            'ownerRecord' => $record,
                            'pageClass' => $livewire::class,
                        ])->hiddenOn(['create']),
                        Forms\Components\Section::make(__('Other settings'))
                            ->schema([
                                Forms\Components\Toggle::make('settings.create_client')
                                    ->label(__('Create client on form submission'))
                                    ->helperText(__('Enable this to create a client when the form is submitted.'))
                                    ->default(false)
                                    ->live(),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('settings.client_name_field')
                                            ->label(__('Client name field'))
                                            ->relationship('fields', 'label')
                                            ->requiredIf('settings.create_client', true)
                                            ->helperText(__('Select the field to use as the client name.')),
                                        Forms\Components\Select::make('settings.client_email_field')
                                            ->label(__('Client email field'))
                                            ->relationship('fields', 'label')
                                            ->requiredIf('settings.create_client', true)
                                            ->helperText(__('Select the field to use as the client email.')),
                                    ])
                                    ->hidden(fn (Get $get) => ! $get('settings.create_client')),
                            ]),
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
                                    ->default(TicketPriority::NORMAL)
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
            'index' => \App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\ListForms::route('/'),
            'create' => \App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\CreateForm::route('/create'),
            'edit' => \App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
