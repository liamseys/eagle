<?php

namespace App\Models;

use App\Enums\Tickets\TicketActivityColumn;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    use HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_activity';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'authorable_type',
        'authorable_id',
        'column',
        'value',
        'reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'column' => TicketActivityColumn::class,
        ];
    }

    /**
     * Ticket activity belongs to a ticket.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Ticket activity author (user or other model).
     */
    public function authorable()
    {
        return $this->morphTo();
    }
}
