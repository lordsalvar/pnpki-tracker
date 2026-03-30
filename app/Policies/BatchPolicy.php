<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Batch $batch): bool
    {
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        return $user->office_id === $batch->office_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::REPRESENTATIVE->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Batch $batch): bool
    {
        return $user->role === UserRole::REPRESENTATIVE->value;
    }

    /**
     * Determine whether the user can mark a batch for submission.
     */
    public function markForSubmission(User $user, Batch $batch): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Batch $batch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Batch $batch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Batch $batch): bool
    {
        return false;
    }
}
