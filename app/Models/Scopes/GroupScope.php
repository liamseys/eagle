<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GroupScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && ! auth()->user()->hasPermissionTo('settings')) {
            $builder->whereIn('group_id', auth()->user()->groups()->pluck('id')->toArray());
        }
    }
}
