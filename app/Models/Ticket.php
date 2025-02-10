<?php

namespace App\Models;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Observers\TicketObserver;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TicketObserver::class])]
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory, HasNotes, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'requester_id',
        'assignee_id',
        'group_id',
        'subject',
        'priority',
        'type',
        'status',
        'is_escalated',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'type' => TicketType::class,
            'status' => TicketStatus::class,
            'is_escalated' => 'boolean',
        ];
    }

    /**
     * The client that the ticket belongs to.
     *
     * @return BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user that the ticket is assigned to.
     *
     * @return BelongsTo
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * The group that the ticket is assigned to.
     *
     * @return BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * A ticket has many comments.
     *
     * @return HasMany
     */
    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    /**
     * A ticket has many SLAs.
     *
     * @return HasMany
     */
    public function slas()
    {
        return $this->hasMany(TicketSla::class);
    }

    /**
     * A ticket has many fields.
     *
     * @return HasMany
     */
    public function fields()
    {
        return $this->hasMany(TicketField::class);
    }

    /**
     * A ticket has many ticket activity.
     */
    public function activity(): HasMany
    {
        return $this->hasMany(TicketActivity::class);
    }

    /**
     * Limit the query to solved tickets.
     */
    public function scopeSolved(Builder $query): void
    {
        $query->whereIn('status', [TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value]);
    }

    /**
     * Limit the query to unsolved tickets.
     */
    public function scopeUnsolved(Builder $query): void
    {
        $query->whereIn('status', [TicketStatus::OPEN->value, TicketStatus::PENDING->value, TicketStatus::ON_HOLD->value]);
    }
}
