<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Percent;
use Illuminate\Auth\Access\HandlesAuthorization;

class PercentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_percent');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Percent $percent): bool
    {
        return $user->can('view_percent');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_percent');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Percent $percent): bool
    {
        return $user->can('update_percent');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Percent $percent): bool
    {
        return $user->can('delete_percent');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_percent');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Percent $percent): bool
    {
        return $user->can('force_delete_percent');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_percent');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Percent $percent): bool
    {
        return $user->can('restore_percent');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_percent');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Percent $percent): bool
    {
        return $user->can('replicate_percent');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_percent');
    }
}
