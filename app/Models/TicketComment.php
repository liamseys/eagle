<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TicketComment extends Model
{
    /** @use HasFactory<\Database\Factories\TicketCommentFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'authorable_type',
        'authorable_id',
        'body',
        'is_public',
    ];

    /**
     * A ticket comment belongs to a ticket.
     *
     * @return BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * A ticket comment belongs to an author.
     *
     * @return MorphTo
     */
    public function authorable()
    {
        return $this->morphTo();
    }

    /**
     * Check if the comment was made by the requester.
     */
    public function isRequester(): bool
    {
        return $this->authorable->id === $this->ticket->requester_id;
    }
}
