<?php

namespace App\Support;

use App\Models\Ticket;
use Closure;

/**
 * Centralized definition of merge tags available in ticket comment rich
 * content. Both the editor (label list) and the renderer (value resolver)
 * are configured from this class so the available tags stay in sync.
 */
class TicketMergeTags
{
    /**
     * Merge tag names mapped to their human-readable labels.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'client.name' => __('Client name'),
            'client.email' => __('Client email'),
            'ticket.id' => __('Ticket ID'),
            'ticket.subject' => __('Ticket subject'),
            'ticket.status' => __('Ticket status'),
            'agent.name' => __('Agent name'),
            'today' => __('Today\'s date'),
        ];
    }

    /**
     * Merge tag names mapped to value-resolving closures for rendering.
     *
     * Closures are only evaluated for tags that appear in the content, and
     * each is cached after the first lookup by `RichContentRenderer`.
     *
     * @return array<string, Closure>
     */
    public static function valuesFor(?Ticket $ticket): array
    {
        return [
            'client.name' => fn (): string => $ticket?->requester?->name ?? '',
            'client.email' => fn (): string => $ticket?->requester?->email ?? '',
            'ticket.id' => fn (): string => (string) ($ticket?->ticket_id ?? ''),
            'ticket.subject' => fn (): string => $ticket?->subject ?? '',
            'ticket.status' => fn (): string => $ticket?->status?->value ?? '',
            'agent.name' => fn (): string => auth()->user()?->name ?? '',
            'today' => fn (): string => now()->toFormattedDateString(),
        ];
    }
}
