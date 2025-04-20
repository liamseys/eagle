<?php

namespace App\Models;

use App\Filament\AvatarProviders\GravatarProvider;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Tags\HasTags;

class Client extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, HasNotes, HasTags, HasUlids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'locale',
        'timezone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * A client can have many tickets.
     *
     * @return HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    /**
     * A client belongs to many groups.
     *
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class)
            ->withTimestamps();
    }

    /**
     * Retrieve the client's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return app(GravatarProvider::class)->get($this);
    }
}
