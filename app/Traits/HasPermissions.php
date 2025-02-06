<?php

namespace App\Traits;

use App\Models\Permission;

trait HasPermissions
{
    /**
     * Model has many permissions.
     * `
     *
     * @return mixed
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @return mixed
     */
    public function hasPermissionTo($permission)
    {
        return $this->permissions->contains('name', $permission);
    }
}
