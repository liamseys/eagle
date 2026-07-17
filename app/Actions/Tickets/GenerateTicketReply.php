<?php

namespace App\Actions\Tickets;

use App\Ai\Agents\TicketReplyDrafter;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Support\HtmlToText;

final class GenerateTicketReply
{
    /**
     * Draft the next reply for the ticket in the agent's own writing style.
     *
     * Returns sanitized HTML ready to insert into the reply editor. The draft
     * is never sent anywhere; the agent reviews and submits it manually.
     */
    public function handle(Ticket $ticket, User $agent): string
    {
        $response = (new TicketReplyDrafter($ticket, $agent))->prompt($this->buildPrompt($ticket));

        return $this->sanitize($response->text);
    }

    /**
     * Compose the ticket context and conversation transcript for the AI.
     */
    private function buildPrompt(Ticket $ticket): string
    {
        $ticket->loadMissing(['requester', 'comments.authorable', 'fields.formField']);

        $details = collect([
            'Subject: '.$ticket->subject,
            'Status: '.$ticket->status?->value,
            'Priority: '.$ticket->priority?->value,
            'Type: '.$ticket->type?->value,
            'Requester: '.($ticket->requester?->name ?? __('Unknown')),
        ])->implode("\n");

        $fields = $ticket->fields
            ->filter(fn ($field): bool => $field->formField !== null && filled($field->value))
            ->map(fn ($field): string => "- {$field->formField->label}: ".HtmlToText::convert((string) $field->value));

        $transcript = $ticket->comments
            ->sortBy('created_at')
            ->values()
            ->map(function (TicketComment $comment): string {
                $author = $comment->authorable?->name ?? 'Unknown';

                $label = match (true) {
                    ! $comment->is_public => "Internal note by {$author} (context only, never reveal to the customer)",
                    $comment->authorable instanceof User => "Agent {$author}",
                    default => "Customer {$author}",
                };

                return "{$label}:\n".HtmlToText::convert($comment->body);
            })
            ->implode("\n\n");

        return collect([
            "Ticket details:\n{$details}",
            $fields->isNotEmpty() ? "Submitted form fields:\n".$fields->implode("\n") : null,
            $transcript === ''
                ? 'There are no messages in the conversation yet.'
                : "Conversation so far:\n\n{$transcript}",
            'Draft the next reply from the agent to the customer.',
        ])->filter()->implode("\n\n");
    }

    /**
     * Reduce the model output to the plain formatting the reply editor uses.
     *
     * The draft only ever renders inside the authoring agent's own editor and
     * is re-rendered through RichContentRenderer on submit, but stray markdown
     * fences and disallowed tags are stripped so the inserted draft is clean.
     */
    private function sanitize(string $reply): string
    {
        $reply = preg_replace('/^\s*```[a-z]*\s*|\s*```\s*$/i', '', trim($reply)) ?? $reply;

        return trim(strip_tags($reply, '<p><br><ul><ol><li><strong><em><u>'));
    }
}
