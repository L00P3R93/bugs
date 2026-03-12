<?php

namespace App\Policies;

use App\Models\Bug;
use App\Models\User;

class BugPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_bugs');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bug $bugs): bool
    {
        return $user->hasPermissionTo('view_bug');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_bug');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bug $bugs): bool
    {
        return $user->hasPermissionTo('update_bug');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bug $bugs): bool
    {
        return $user->hasPermissionTo('delete_bug');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bug $bugs): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bug $bugs): bool
    {
        return false;
    }
}
