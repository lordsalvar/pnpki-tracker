<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy
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
    public function view(User $user, FormSubmission $formSubmission): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        if (UserRole::REPRESENTATIVE->value === $user->role) {
            return $formSubmission->office_id === $user->office_id;
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
    public function update(User $user, FormSubmission $formSubmission): bool
    {
        if (in_array($user->role, [UserRole::ADMIN->value, UserRole::REPRESENTATIVE->value])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FormSubmission $formSubmission): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FormSubmission $formSubmission): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FormSubmission $formSubmission): bool
    {
        if (UserRole::ADMIN->value === $user->role) {
            return true;
        }

        return false;
    }
}
