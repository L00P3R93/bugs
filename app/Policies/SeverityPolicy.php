<?php

namespace App\Policies;

use App\Models\Severity;
use App\Models\User;

class SeverityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_severities');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Severity $severity): bool
    {
        return $user->hasPermissionTo('view_severity');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_severity');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Severity $severity): bool
    {
        return $user->hasPermissionTo('update_severity');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Severity $severity): bool
    {
        return $user->hasPermissionTo('delete_severity');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Severity $severity): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Severity $severity): bool
    {
        return false;
    }
}
