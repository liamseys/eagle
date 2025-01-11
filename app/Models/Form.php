<?php

namespace App\Models;

use App\Observers\FormObserver;
use App\Traits\HasActiveScope;
use App\Traits\HasPublicScope;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([FormObserver::class])]
class Form extends Model
{
    /** @use HasFactory<\Database\Factories\FormFactory> */
    use HasActiveScope, HasFactory, HasPublicScope, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'sort',
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
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
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
     * A form has many fields.
     *
     * @return HasMany
     */
    public function fields()
    {
        return $this->hasMany(FormField::class);
    }
}
