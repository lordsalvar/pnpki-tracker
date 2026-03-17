<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN->value, UserRole::REPRESENTATIVE->value]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Form $form): bool
    {
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }
        if ($user->role === UserRole::REPRESENTATIVE->value) {
            return $form->user->office_id === $user->office_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN->value, UserRole::REPRESENTATIVE->value]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Form $form): bool
    {
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        if ($user->role === UserRole::REPRESENTATIVE->value) {
            return $form->office_id === $user->office_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Form $form): bool
    {
        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        if ($user->role === UserRole::REPRESENTATIVE->value) {
            return $form->office_id === $user->office_id;
        }

        return false; // Representatives are not allowed to delete forms
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Form $form): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Form $form): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }
}
