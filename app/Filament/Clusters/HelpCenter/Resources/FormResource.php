<?php

namespace App\Filament\Clusters\HelpCenter\Resources;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Filament\Clusters\HelpCenter;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\CreateForm;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\EditForm;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\ListForms;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\RelationManagers\FieldsRelationManager;
use App\Models\HelpCenter\Form;
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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $cluster = HelpCenter::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(255),
                                RichEditor::make('description')
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
                        Section::make(__('Other settings'))
                            ->schema([
                                Toggle::make('settings.create_client')
                                    ->label(__('Create client on form submission'))
                                    ->helperText(__('Enable this to create a client when the form is submitted.'))
                                    ->default(false)
                                    ->live()
                                    ->hiddenOn(['create']),
                                Grid::make()
                                    ->schema([
                                        Select::make('settings.client_name_field')
                                            ->label(__('Client name field'))
                                            ->options(function ($livewire) {
                                                return $livewire->record?->fields()
                                                    ->orderBy('sort')
                                                    ->pluck('label', 'name')
                                                    ->toArray();
                                            })
                                            ->requiredIf('settings.create_client', true)
                                            ->helperText(__('Select the field to use as the client name.')),
                                        Select::make('settings.client_email_field')
                                            ->label(__('Client email field'))
                                            ->options(function ($livewire) {
                                                return $livewire->record?->fields()
                                                    ->orderBy('sort')
                                                    ->pluck('label', 'name')
                                                    ->toArray();
                                            })
                                            ->requiredIf('settings.create_client', true)
                                            ->helperText(__('Select the field to use as the client email.')),
                                    ])
                                    ->hidden(fn (Get $get) => ! $get('settings.create_client')),
                                Toggle::make('settings.require_escalation')
                                    ->label(__('Require escalation'))
                                    ->helperText(__('Clients must work with an Account Manager for tickets from this form.'))
                                    ->default(false),
                                Toggle::make('settings.client_portal_featured')
                                    ->label(__('Show in client portal'))
                                    ->helperText(__('This form will be displayed in the client portal.'))
                                    ->default(false),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make(__('Associations'))
                            ->schema([
                                Select::make('default_group_id')
                                    ->label(__('Default group'))
                                    ->relationship('defaultGroup', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('(Optional) When the form is submitted, the ticket will be assigned to this group.')),
                                Select::make('default_ticket_priority')
                                    ->label(__('Default ticket priority'))
                                    ->options(TicketPriority::class)
                                    ->default(TicketPriority::NORMAL)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText(__('The default priority assigned to tickets created from this form.')),
                                Select::make('default_ticket_type')
                                    ->label(__('Default ticket type'))
                                    ->options(TicketType::class)
                                    ->default(TicketType::TASK)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText(__('The default type assigned to tickets created from this form.')),
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
                                    ->helperText(__('(Optional) Select the section for this form.')),
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
                                    )
                                    ->helperText(__('Only clients in this group will be able to see this form.')),
                            ]),
                        Section::make(__('Status'))
                            ->schema([
                                Toggle::make('is_public')
                                    ->label(__('Public'))
                                    ->required()
                                    ->helperText(__('Public forms are visible to everyone.')),
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->required(),
                            ]),
                        Section::make(__('Metadata'))
                            ->schema([
                                Placeholder::make('created_by')
                                    ->label(__('Created by'))
                                    ->content(fn (Form $record): ?string => $record->user?->name),

                                Placeholder::make('created_at')
                                    ->label(__('Created at'))
                                    ->content(fn (Form $record): ?string => $record->created_at?->diffForHumans()),

                                Placeholder::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->content(fn (Form $record): ?string => $record->updated_at?->diffForHumans()),
                            ])->hiddenOn(['create']),
                        Section::make(__('Embed'))
                            ->schema([
                                Toggle::make('is_embeddable')
                                    ->label(__('Allow embedding'))
                                    ->default(false)
                                    ->required()
                                    ->live(),
                                Textarea::make('embed_code')
                                    ->label(__('Embed code'))
                                    ->autosize()
                                    ->helperText(__('Copy and paste this code into your website to embed the form.'))
                                    ->visible(fn ($get) => $get('is_embeddable'))
                                    ->readOnly()
                                    ->afterStateHydrated(function (Textarea $component, $state, $record) {
                                        if ($record) {
                                            $component->state(sprintf(
                                                '<iframe src="%s" width="100%%" height="600" frameborder="0" allowfullscreen></iframe>',
                                                route('forms.embed', [
                                                    'locale' => config('app.locale'),
                                                    'form' => $record,
                                                ])
                                            ));
                                        }
                                    }),
                            ])->hiddenOn(['create']),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
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
            'index' => ListForms::route('/'),
            'create' => CreateForm::route('/create'),
            'edit' => EditForm::route('/{record}/edit'),
        ];
    }
}
