<?php

namespace App\Models;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketSlaStatus;
use App\Enums\Tickets\TicketSlaType;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Observers\TicketObserver;
use App\Settings\WorkflowSettings;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
            'tags' => 'array',
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
     * Create SLAs for the ticket.
     *
     * @return void
     */
    public function createSlas()
    {
        $workflowSettings = app(WorkflowSettings::class);

        $slaPolicies = collect($workflowSettings->sla_policies);

        $slaPolicy = $slaPolicies->firstWhere('priority', $this->priority);

        $this->slas()->createMany([[
            'group_id' => $this->group_id,
            'type' => TicketSlaType::INITIAL_RESPONSE,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($slaPolicy['first_response_time']),
        ], [
            'group_id' => $this->group_id,
            'type' => TicketSlaType::NEXT_RESPONSE,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($slaPolicy['every_response_time']),
        ], [
            'group_id' => $this->group_id,
            'type' => TicketSlaType::RESOLUTION,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($slaPolicy['resolution_time']),
        ]]);
    }

    /**
     * Close the SLAs for the ticket.
     *
     * @return void
     */
    public function closeSlas()
    {
        foreach ($this->slas as $ticketSla) {
            $ticketSla->update([
                'status' => TicketSlaStatus::CLOSED,
            ]);
        }
    }
}
