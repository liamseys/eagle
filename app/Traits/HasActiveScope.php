<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveScope
{
    /**
     * Scope a query to only include active records.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', '=', true);
    }
}
