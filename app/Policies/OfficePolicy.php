<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Office;
use App\Models\User;

class OfficePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::ADMIN->value,
            UserRole::REPRESENTATIVE->value,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Office $office): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        if (UserRole::REPRESENTATIVE->value === $user->role) {
            return $user->office_id === $office->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Office $office): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        // if (UserRole::REPRESENTATIVE->value === $user->role) {
        //     return true;
        // }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Office $office): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Office $office): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Office $office): bool
    {
        return false;
    }
}
