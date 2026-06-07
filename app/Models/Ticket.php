<?php

namespace App\Models;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Models\Scopes\ClientScope;
use App\Models\Scopes\GroupScope;
use App\Observers\TicketObserver;
use App\Services\Sla\SlaStatusFactory;
use App\Support\Sla\TicketSla;
use App\Traits\HasNotes;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TicketObserver::class])]
#[ScopedBy([GroupScope::class, ClientScope::class])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
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
        'duplicate_of_ticket_id',
        'subject',
        'priority',
        'type',
        'status',
        'is_escalated',
        'scheduled_close_at',
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
            'scheduled_close_at' => 'datetime',
            'first_response_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sla_alerts' => 'array',
        ];
    }

    /**
     * The live SLA status (deadlines, achievements and breach state) for the ticket.
     *
     * The calculation lives in the factory so the model stays free of SLA logic.
     */
    public function sla(): TicketSla
    {
        return app(SlaStatusFactory::class)->for($this);
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
     * Get the main ticket that this ticket is a duplicate of.
     *
     * This defines a relationship where the current ticket was marked as a duplicate
     * of another ticket (the main/original one). Useful for tracing merged tickets.
     *
     * @return BelongsTo
     */
    public function duplicateOf()
    {
        return $this->belongsTo(Ticket::class, 'duplicate_of_ticket_id', 'id');
    }

    /**
     * Get the support email address including the ticket ID.
     *
     * This method appends the ticket ID to the default support email address
     * in the format: support+{ticket_id}@example.com.
     */
    public function getSupportEmailWithTicketId(): ?string
    {
        if (! $this->ticket_id || ! config('mail.from.address')) {
            return config('mail.from.address');
        }

        return preg_replace('/^(.*)@/', "support+{$this->ticket_id}@", config('mail.from.address'));
    }

    /**
     * Limit the query to solved tickets.
     */
    public function scopeSolved(Builder $query): void
    {
        $query->whereIn('status', [TicketStatus::SOLVED->value, TicketStatus::CLOSED->value]);
    }

    /**
     * Limit the query to unsolved tickets.
     */
    public function scopeUnsolved(Builder $query): void
    {
        $query->whereIn('status', [TicketStatus::NEW->value, TicketStatus::OPEN->value, TicketStatus::PENDING->value, TicketStatus::ON_HOLD->value]);
    }
}
