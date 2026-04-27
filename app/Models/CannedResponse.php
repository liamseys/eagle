<?php

namespace App\Models;

use Database\Factories\CannedResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedResponse extends Model
{
    /** @use HasFactory<CannedResponseFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'canned_response_category_id',
        'title',
        'content',
        'is_shared',
        'last_used_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_shared' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * The user that created the canned response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The category the canned response belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CannedResponseCategory::class, 'canned_response_category_id');
    }

    /**
     * Limit the query to canned responses visible to the given user.
     *
     * Visible responses are those owned by the user or shared with all agents.
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id)
                ->orWhere('is_shared', true);
        });
    }

    /**
     * Limit the query to private (non-shared) responses.
     */
    public function scopePrivate(Builder $query): void
    {
        $query->where('is_shared', false);
    }

    /**
     * Limit the query to shared responses.
     */
    public function scopeShared(Builder $query): void
    {
        $query->where('is_shared', true);
    }
}
