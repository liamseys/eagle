<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPublicScope
{
    /**
     * Scope a query to only include public records.
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_public', '=', true);
    }
}
