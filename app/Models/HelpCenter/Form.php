<?php

namespace App\Models\HelpCenter;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Models\Group;
use App\Models\User;
use App\Observers\FormObserver;
use App\Traits\HasActiveScope;
use App\Traits\HasPublicScope;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([FormObserver::class])]
class Form extends Model
{
    /** @use HasFactory<\Database\Factories\HelpCenter\FormFactory> */
    use HasActiveScope, HasFactory, HasPublicScope, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hc_forms';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'section_id',
        'slug',
        'name',
        'description',
        'default_group_id',
        'default_ticket_priority',
        'default_ticket_type',
        'settings',
        'sort',
        'is_embeddable',
        'is_public',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_ticket_priority' => TicketPriority::class,
            'default_ticket_type' => TicketType::class,
            'settings' => 'array',
            'is_embeddable' => 'boolean',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * A form belongs to a user.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The default group for the form.
     * When submitted, the generated ticket will be assigned to this group.
     *
     * @return BelongsTo
     */
    public function defaultGroup()
    {
        return $this->belongsTo(Group::class, 'default_group_id');
    }

    /**
     * The section for the form.
     *
     * @return BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * A form belongs to many groups.
     *
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class)
            ->withTimestamps();
    }

    /**
     * A form has many fields.
     *
     * @return HasMany
     */
    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    /**
     * Get the label attribute of the instance.
     */
    public function getLabelAttribute(): string
    {
        return $this->name;
    }
}
