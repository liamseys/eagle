<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Client $user): bool
    {
        if ($user instanceof User) {
            return $user->hasPermissionTo('tickets');
        }

        return true;
    }
}
