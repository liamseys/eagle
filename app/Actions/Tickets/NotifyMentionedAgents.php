<?php

namespace App\Actions\Tickets;

use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\MentionedInTicketComment;
use App\Support\RichEditor\AgentMentions;

final class NotifyMentionedAgents
{
    /**
     * Notify the agents mentioned in a ticket comment.
     *
     * Mentions are extracted from the raw editor content, de-duplicated so an
     * agent mentioned multiple times is notified once, and the comment author
     * is never notified about mentioning themselves.
     */
    public function handle(TicketComment $ticketComment, ?string $editorContent, User $author): void
    {
        $mentionedIds = collect(AgentMentions::mentionedUserIds($editorContent))
            ->reject(fn (string $id): bool => $id === (string) $author->id);

        if ($mentionedIds->isEmpty()) {
            return;
        }

        User::query()
            ->whereIn('id', $mentionedIds)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $agent) => $agent->notify(new MentionedInTicketComment($ticketComment)));
    }
}
