<?php

namespace App\Support\RichEditor;

use App\Actions\Tickets\GenerateTicketReply;
use App\Models\Ticket;
use App\Models\User;
use App\Support\HtmlToText;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;
use Livewire\Component as LivewireComponent;
use Throwable;
use Tiptap\Core\Extension;

/**
 * Filament RichEditor plugin that contributes an "AI Reply" toolbar button.
 *
 * Clicking it drafts a reply to the ticket conversation in the agent's own
 * writing style and inserts it into the editor. The draft is only ever placed
 * in the editor for review — it is never sent automatically. When the editor
 * already contains a draft, a confirmation modal is shown first because
 * generating replaces the current content.
 *
 * The host Livewire component must expose the ticket as a public `$ticket`
 * property (as `CreateTicketComment` does).
 */
class AiReplyPlugin implements RichContentPlugin
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
            RichEditorTool::make('aiReply')
                ->label(__('AI Reply'))
                ->icon(Heroicon::Sparkles)
                ->extraAttributes(['class' => 'fi-fo-rich-editor-tool-ai-reply'])
                ->jsHandler(fn (RichEditorTool $tool): string => $this->toolJsHandler($tool)),
        ];
    }

    /**
     * The toolbar button's click handler.
     *
     * Equivalent to the default `RichEditorTool::action()` handler, but wraps
     * the `mountAction` call with a `data-ai-reply-generating` attribute on the
     * editor root so CSS can show a "Generating reply…" overlay, spin the
     * toolbar icon, and lock the editor while the request is in flight. The
     * attribute carries the translated status message for the overlay, and the
     * guard prevents double-triggering during generation.
     */
    protected function toolJsHandler(RichEditorTool $tool): string
    {
        $message = Js::from(__('Generating reply…'));
        $context = Js::from(['schemaComponent' => $tool->getEditor()->getKey()]);

        return "const aiReplyRoot = \$el.closest('.fi-fo-rich-editor'); "
            ."if (aiReplyRoot && ! aiReplyRoot.hasAttribute('data-ai-reply-generating')) { "
            ."aiReplyRoot.setAttribute('data-ai-reply-generating', {$message}); "
            ."\$wire.mountAction('aiReply', { editorSelection }, {$context})"
            .".finally(() => aiReplyRoot.removeAttribute('data-ai-reply-generating')); "
            .'}';
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            $this->aiReplyAction(),
        ];
    }

    protected function aiReplyAction(): Action
    {
        return Action::make('aiReply')
            ->requiresConfirmation()
            // Skip the confirmation modal entirely when there is no draft to replace.
            ->modalHidden(fn (RichEditor $component, LivewireComponent $livewire): bool => ! $this->editorHasContent($component, $livewire))
            ->modalIcon(Heroicon::OutlinedSparkles)
            ->modalHeading(__('Replace current draft?'))
            ->modalDescription(__('Generating an AI reply will replace what you have written so far.'))
            ->modalSubmitActionLabel(__('Generate reply'))
            ->action(function (RichEditor $component, LivewireComponent $livewire, GenerateTicketReply $generateTicketReply): void {
                $user = auth()->user();
                $ticket = $livewire->ticket ?? null;

                if (! $user instanceof User || ! $ticket instanceof Ticket) {
                    return;
                }

                try {
                    $reply = $generateTicketReply->handle($ticket, $user);
                } catch (Throwable $exception) {
                    report($exception);

                    Notification::make()
                        ->danger()
                        ->title(__('Could not generate a reply'))
                        ->body(__('Something went wrong while drafting the reply. Please try again.'))
                        ->send();

                    return;
                }

                if (blank($reply)) {
                    Notification::make()
                        ->warning()
                        ->title(__('No reply generated'))
                        ->body(__('The AI could not draft a reply for this conversation. Please write one manually.'))
                        ->send();

                    return;
                }

                data_set($livewire, $component->getStatePath(), $reply);

                Notification::make()
                    ->success()
                    ->title(__('Draft ready'))
                    ->body(__('Review and edit the AI draft before sending it.'))
                    ->send();
            });
    }

    /**
     * Whether the editor currently contains actual text.
     *
     * The editor state may be raw HTML or a TipTap document array, and an
     * "empty" editor still holds an empty document, so both are reduced to
     * plain text before checking.
     */
    protected function editorHasContent(RichEditor $component, LivewireComponent $livewire): bool
    {
        $state = data_get($livewire, $component->getStatePath());

        if (blank($state)) {
            return false;
        }

        return filled(HtmlToText::convert(RichContentRenderer::make($state)->toHtml()));
    }
}
