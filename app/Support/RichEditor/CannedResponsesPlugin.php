<?php

namespace App\Support\RichEditor;

use App\Models\CannedResponse;
use App\Models\CannedResponseCategory;
use App\Models\User;
use App\Support\TicketMergeTags;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Tiptap\Core\Extension;

/**
 * Filament RichEditor plugin that contributes a "Canned responses" toolbar
 * button and the nested CRUD actions backing it.
 *
 * The plugin registers a single editor-level action (`cannedResponses`); all
 * Create / Edit / Delete / Manage flows are nested inside that action's
 * modal footer so the entire feature lives in a single mount-point with no
 * Filament Resources.
 */
class CannedResponsesPlugin implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('cannedResponses')
                ->label(__('Canned responses'))
                ->icon(Heroicon::ChatBubbleLeftRight)
                ->action(),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            $this->pickerAction(),
        ];
    }

    /**
     * Main picker modal: search, filter, list, insert.
     */
    protected function pickerAction(): Action
    {
        return Action::make('cannedResponses')
            ->modalHeading(__('Canned responses'))
            ->modalDescription(__('Insert a saved response into your reply.'))
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel(__('Insert'))
            ->fillForm([
                'search' => null,
                'canned_response_category_id' => null,
                'canned_response_id' => null,
            ])
            ->schema(fn (): array => $this->pickerSchema())
            ->extraModalFooterActions(fn (): array => [
                $this->createAction(),
                $this->editAction(),
                $this->manageCategoriesAction(),
            ])
            ->action(function (array $data, RichEditor $component, array $arguments): void {
                $this->insertResponse($data['canned_response_id'] ?? null, $component, $arguments);
            });
    }

    /**
     * @return array<Component>
     */
    protected function pickerSchema(): array
    {
        return [
            Grid::make()
                ->columns(2)
                ->schema([
                    TextInput::make('search')
                        ->label(__('Search'))
                        ->placeholder(__('Search title or content...'))
                        ->prefixIcon(Heroicon::MagnifyingGlass)
                        ->live(debounce: 300),
                    Select::make('canned_response_category_id')
                        ->label(__('Category'))
                        ->placeholder(__('All categories'))
                        ->options(fn (): array => CannedResponseCategory::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->live(),
                ]),
            Radio::make('canned_response_id')
                ->hiddenLabel()
                ->required()
                ->columns(1)
                ->options(fn (Get $get): array => $this->buildOptions($get))
                ->descriptions(fn (Get $get): array => $this->buildDescriptions($get)),
        ];
    }

    /**
     * Schema reused for both "Create" and "Edit" canned response forms.
     *
     * @return array<Component>
     */
    protected function formSchema(): array
    {
        return [
            Grid::make()
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label(__('Title'))
                        ->required()
                        ->maxLength(255),
                    Select::make('canned_response_category_id')
                        ->label(__('Category'))
                        ->placeholder(__('No category'))
                        ->options(fn (): array => CannedResponseCategory::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()),
                ]),
            RichEditor::make('content')
                ->label(__('Content'))
                ->mergeTags(TicketMergeTags::labels())
                ->required(),
            Toggle::make('is_shared')
                ->label(__('Share with all agents'))
                ->helperText(__('Shared responses are visible to every agent. Private responses are only visible to you.'))
                ->default(false),
        ];
    }

    protected function createAction(): Action
    {
        return Action::make('createCannedResponse')
            ->label(__('New'))
            ->icon(Heroicon::Plus)
            ->color('primary')
            ->modalHeading(__('New canned response'))
            ->modalWidth(Width::TwoExtraLarge)
            ->slideOver()
            ->fillForm(['is_shared' => false])
            ->schema($this->formSchema())
            ->action(function (array $data): void {
                $user = auth()->user();

                if (! $user instanceof User) {
                    return;
                }

                CannedResponse::create([
                    ...$data,
                    'user_id' => $user->id,
                ]);

                Notification::make()
                    ->title(__('Canned response created.'))
                    ->success()
                    ->send();
            });
    }

    protected function editAction(): Action
    {
        return Action::make('editCannedResponse')
            ->label(__('Edit selected'))
            ->icon(Heroicon::PencilSquare)
            ->color('gray')
            ->modalHeading(__('Edit canned response'))
            ->modalWidth(Width::TwoExtraLarge)
            ->slideOver()
            ->mountUsing(function (array $mountedActions): void {
                if ($this->resolveSelectedResponse($mountedActions)) {
                    return;
                }

                Notification::make()
                    ->title(__('Select a canned response to edit first.'))
                    ->warning()
                    ->send();
            })
            ->fillForm(function (array $mountedActions): array {
                $response = $this->resolveSelectedResponse($mountedActions);

                if (! $response) {
                    return [];
                }

                return [
                    'title' => $response->title,
                    'canned_response_category_id' => $response->canned_response_category_id,
                    'content' => $response->content,
                    'is_shared' => $response->is_shared,
                ];
            })
            ->schema($this->formSchema())
            ->action(function (array $data, array $mountedActions): void {
                $response = $this->resolveSelectedResponse($mountedActions);

                if (! $response) {
                    return;
                }

                $user = auth()->user();

                if (! $this->canManage($user, $response)) {
                    Notification::make()
                        ->title(__('You cannot edit this canned response.'))
                        ->danger()
                        ->send();

                    return;
                }

                $response->update($data);

                Notification::make()
                    ->title(__('Canned response updated.'))
                    ->success()
                    ->send();
            })
            ->extraModalFooterActions(fn (): array => [
                Action::make('deleteCannedResponse')
                    ->label(__('Delete'))
                    ->icon(Heroicon::Trash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('Delete canned response?'))
                    ->action(function (array $mountedActions): void {
                        $response = $this->resolveSelectedResponse($mountedActions);

                        if (! $response) {
                            return;
                        }

                        $user = auth()->user();

                        if (! $this->canManage($user, $response)) {
                            Notification::make()
                                ->title(__('You cannot delete this canned response.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $response->delete();

                        Notification::make()
                            ->title(__('Canned response deleted.'))
                            ->success()
                            ->send();
                    })
                    ->cancelParentActions('editCannedResponse'),
            ]);
    }

    protected function manageCategoriesAction(): Action
    {
        return Action::make('manageCannedResponseCategories')
            ->label(__('Manage categories'))
            ->icon(Heroicon::FolderOpen)
            ->color('gray')
            ->modalHeading(__('Manage categories'))
            ->modalWidth(Width::Large)
            ->slideOver()
            ->modalSubmitActionLabel(__('Save'))
            ->fillForm(fn (): array => [
                'categories' => CannedResponseCategory::query()
                    ->orderBy('name')
                    ->get()
                    ->map(fn (CannedResponseCategory $category): array => [
                        'id' => $category->id,
                        'name' => $category->name,
                    ])
                    ->all(),
            ])
            ->schema([
                Repeater::make('categories')
                    ->hiddenLabel()
                    ->addActionLabel(__('Add category'))
                    ->reorderable(false)
                    ->schema([
                        TextInput::make('name')
                            ->hiddenLabel()
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('Category name')),
                        TextInput::make('id')
                            ->hidden()
                            ->dehydrated(),
                    ])
                    ->columns(1),
            ])
            ->action(function (array $data): void {
                $rows = collect($data['categories'] ?? []);

                $submittedIds = $rows->pluck('id')->filter()->all();
                $existingIds = CannedResponseCategory::query()->pluck('id')->all();

                CannedResponseCategory::query()
                    ->whereIn('id', array_values(array_diff($existingIds, $submittedIds)))
                    ->delete();

                foreach ($rows as $row) {
                    if (filled($row['id'] ?? null)) {
                        CannedResponseCategory::query()
                            ->where('id', $row['id'])
                            ->update(['name' => $row['name']]);

                        continue;
                    }

                    CannedResponseCategory::create(['name' => $row['name']]);
                }

                Notification::make()
                    ->title(__('Categories saved.'))
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<string, string>
     */
    protected function buildOptions(Get $get): array
    {
        return self::buildQuery(
            user: $this->currentAgent(),
            search: $get('search'),
            categoryId: $get('canned_response_category_id'),
        )
            ->limit(25)
            ->get()
            ->mapWithKeys(fn (CannedResponse $response): array => [
                $response->id => $response->title,
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function buildDescriptions(Get $get): array
    {
        return self::buildQuery(
            user: $this->currentAgent(),
            search: $get('search'),
            categoryId: $get('canned_response_category_id'),
        )
            ->limit(25)
            ->get()
            ->mapWithKeys(fn (CannedResponse $response): array => [
                $response->id => Str::limit(trim(strip_tags($response->content)), 120),
            ])
            ->all();
    }

    /**
     * Build the query that powers the picker list.
     *
     * Extracted from the form lifecycle so it can be exercised independently
     * of Filament's schema utilities (e.g. in tests).
     */
    public static function buildQuery(?User $user, ?string $search = null, ?string $categoryId = null): Builder
    {
        $query = CannedResponse::query();

        if ($user instanceof User) {
            $query->visibleTo($user);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (filled($search)) {
            $like = '%'.$search.'%';
            $query->where(function (Builder $query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('content', 'like', $like);
            });
        }

        if (filled($categoryId)) {
            $query->where('canned_response_category_id', $categoryId);
        }

        return $query->orderByRaw('last_used_at IS NULL')
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at');
    }

    protected function currentAgent(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function insertResponse(?string $id, RichEditor $component, array $arguments): void
    {
        if (blank($id)) {
            return;
        }

        $response = CannedResponse::find($id);

        if (! $response) {
            return;
        }

        $response->forceFill(['last_used_at' => now()])->saveQuietly();

        $component->runCommands(
            [EditorCommand::make('insertContent', arguments: [$response->content])],
            editorSelection: $arguments['editorSelection'],
        );
    }

    /**
     * @param  array<int, Action>  $mountedActions
     */
    protected function resolveSelectedResponse(array $mountedActions): ?CannedResponse
    {
        $id = $mountedActions[0]?->getRawData()['canned_response_id'] ?? null;

        if (blank($id)) {
            return null;
        }

        return CannedResponse::find($id);
    }

    protected function canManage(?Authenticatable $user, CannedResponse $response): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $response->user_id === $user->id;
    }
}
