<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Prescription;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrescriptionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_prescription');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Prescription $prescription): bool
    {
        return $user->can('view_prescription');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_prescription');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Prescription $prescription): bool
    {
        return $user->can('update_prescription');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prescription $prescription): bool
    {
        return $user->can('delete_prescription');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_prescription');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Prescription $prescription): bool
    {
        return $user->can('force_delete_prescription');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_prescription');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Prescription $prescription): bool
    {
        return $user->can('restore_prescription');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_prescription');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Prescription $prescription): bool
    {
        return $user->can('replicate_prescription');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_prescription');
    }
}
