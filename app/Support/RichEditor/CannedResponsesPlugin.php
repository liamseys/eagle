<?php

namespace App\Support\RichEditor;

use App\Models\CannedResponse;
use App\Models\CannedResponseCategory;
use App\Models\User;
use App\Support\TicketMergeTags;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use Tiptap\Core\Extension;

/**
 * Filament RichEditor plugin that contributes a "Canned responses" toolbar
 * button and the nested CRUD actions backing it.
 *
 * The plugin registers a single editor-level action (`cannedResponses`); all
 * Create / Edit / Delete / Manage flows are nested inside that action's
 * modal so the entire feature lives in a single mount-point with no
 * Filament Resources. Edit and Delete are rendered inline within the list,
 * while Create and Manage Categories sit in the modal footer.
 */
class CannedResponsesPlugin implements RichContentPlugin
{
    /**
     * Maximum number of responses listed in the picker before the user is
     * prompted to refine their search.
     */
    protected const PICKER_LIMIT = 25;

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
            ->modalDescription(__('Selecting a response replaces the current draft.'))
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel(__('Use response'))
            ->modalSubmitAction(fn (Action $action, $livewire): Action => $action
                ->livewire($livewire)
                ->disabled(fn (array $mountedActions): bool => blank(
                    $mountedActions[0]?->getRawData()['canned_response_id'] ?? null,
                )))
            ->fillForm([
                'search' => null,
                'canned_response_category_id' => null,
                'canned_response_id' => null,
            ])
            ->schema(fn (): array => $this->pickerSchema())
            ->extraModalFooterActions(fn (): array => [
                $this->createAction(),
                $this->manageCategoriesAction(),
            ])
            ->registerModalActions([
                $this->editAction(),
                $this->deleteAction(),
            ])
            ->action(function (array $data, RichEditor $component, LivewireComponent $livewire): void {
                $this->applyResponse($data['canned_response_id'] ?? null, $component, $livewire);
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
            Hidden::make('canned_response_id')
                ->required(),
            View::make('filament.canned-responses.list')
                ->viewData(fn (Get $get): array => $this->buildListViewData($get)),
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
            ->label(__('Edit'))
            ->icon(Heroicon::PencilSquare)
            ->color('gray')
            ->modalHeading(__('Edit canned response'))
            ->modalWidth(Width::TwoExtraLarge)
            ->slideOver()
            ->mountUsing(function (array $arguments): void {
                if ($this->resolveResponseFromArguments($arguments)) {
                    return;
                }

                Notification::make()
                    ->title(__('Canned response not found.'))
                    ->warning()
                    ->send();
            })
            ->fillForm(function (array $arguments): array {
                $response = $this->resolveResponseFromArguments($arguments);

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
            ->action(function (array $data, array $arguments): void {
                $response = $this->resolveResponseFromArguments($arguments);

                if (! $response) {
                    return;
                }

                if (! $this->canManage(auth()->user(), $response)) {
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
            });
    }

    protected function deleteAction(): Action
    {
        return Action::make('deleteCannedResponse')
            ->label(__('Delete'))
            ->icon(Heroicon::Trash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalIcon(Heroicon::OutlinedTrash)
            ->modalHeading(__('Delete canned response?'))
            ->modalDescription(fn (array $arguments): ?string => optional($this->resolveResponseFromArguments($arguments))->title)
            ->action(function (array $arguments, array $mountedActions, LivewireComponent $livewire): void {
                $response = $this->resolveResponseFromArguments($arguments);

                if (! $response) {
                    return;
                }

                if (! $this->canManage(auth()->user(), $response)) {
                    Notification::make()
                        ->title(__('You cannot delete this canned response.'))
                        ->danger()
                        ->send();

                    return;
                }

                $deletedId = $response->id;

                $response->delete();

                $this->clearParentSelectionIfMatches($mountedActions, $livewire, $deletedId);

                Notification::make()
                    ->title(__('Canned response deleted.'))
                    ->success()
                    ->send();
            });
    }

    protected function manageCategoriesAction(): Action
    {
        return Action::make('manageCannedResponseCategories')
            ->label(__('Categories'))
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
     * Build the data passed to the picker list Blade view.
     *
     * @return array{responses: Collection<int, array<string, mixed>>, totalAvailable: int}
     */
    protected function buildListViewData(Get $get): array
    {
        $user = $this->currentAgent();

        $query = self::buildQuery(
            user: $user,
            search: $get('search'),
            categoryId: $get('canned_response_category_id'),
        );

        $totalAvailable = (clone $query)->count();

        $responses = $query
            ->with('category')
            ->limit(self::PICKER_LIMIT)
            ->get()
            ->map(fn (CannedResponse $response): array => [
                'id' => $response->id,
                'title' => $response->title,
                'preview' => self::buildPreview($response->content),
                'category' => $response->category?->name,
                'is_shared' => $response->is_shared,
                'can_manage' => $this->canManage($user, $response),
            ]);

        return [
            'responses' => $responses,
            'totalAvailable' => $totalAvailable,
        ];
    }

    /**
     * Build a plain-text preview from saved rich-editor HTML.
     *
     * The editor stores merge tags as empty `<span data-type="mergeTag" ...>`
     * elements (the visible `{{ id }}` text is rendered client-side), so we
     * substitute them back to their placeholder form before stripping tags.
     * Block boundaries are turned into spaces so adjacent paragraphs don't
     * collapse into one word, and HTML entities are decoded so apostrophes
     * and other escaped characters render naturally.
     */
    protected static function buildPreview(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $html = preg_replace_callback(
            '/<span\b[^>]*\bdata-type=(["\'])mergeTag\1[^>]*\bdata-id=(["\'])([^"\']+)\2[^>]*>\s*<\/span>/i',
            fn (array $matches): string => '{{ '.$matches[3].' }}',
            $html,
        ) ?? $html;

        $html = preg_replace('/<\s*br\s*\/?>|<\/(p|div|li|h[1-6]|tr|blockquote)\s*>/i', ' ', $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return Str::limit(trim($text), 120);
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
     * Replace the editor's current draft with the chosen canned response.
     */
    protected function applyResponse(?string $id, RichEditor $component, LivewireComponent $livewire): void
    {
        if (blank($id)) {
            return;
        }

        $response = CannedResponse::find($id);

        if (! $response) {
            return;
        }

        $response->forceFill(['last_used_at' => now()])->saveQuietly();

        data_set($livewire, $component->getStatePath(), $response->content);
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function resolveResponseFromArguments(array $arguments): ?CannedResponse
    {
        $id = $arguments['record'] ?? null;

        if (blank($id)) {
            return null;
        }

        return CannedResponse::find($id);
    }

    /**
     * Clear the picker's selection when the row that was selected has just
     * been deleted, so the "Use response" button can't apply a stale id.
     *
     * @param  array<int, Action>  $mountedActions
     */
    protected function clearParentSelectionIfMatches(array $mountedActions, LivewireComponent $livewire, string $deletedId): void
    {
        $picker = $mountedActions[0] ?? null;

        if (! $picker) {
            return;
        }

        if (($picker->getRawData()['canned_response_id'] ?? null) !== $deletedId) {
            return;
        }

        $nestingIndex = $picker->getNestingIndex();

        if (! isset($livewire->mountedActions[$nestingIndex])) {
            return;
        }

        $livewire->mountedActions[$nestingIndex]['data']['canned_response_id'] = null;
    }

    protected function canManage(?Authenticatable $user, CannedResponse $response): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $response->user_id === $user->id;
    }
}
