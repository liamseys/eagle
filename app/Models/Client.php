<?php

namespace App\Models;

use App\Filament\AvatarProviders\GravatarProvider;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Spatie\Tags\HasTags;

class Client extends Model
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
        'phone',
        'locale',
        'timezone',
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
     * Retrieve the client's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return app(GravatarProvider::class)->get($this);
    }
}
