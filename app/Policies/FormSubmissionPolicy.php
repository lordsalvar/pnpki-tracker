<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
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
     * Flag a finalized submission for revision (finalized batch, not yet for submission to DICT).
     */
    public function flagNeedsRevision(User $user, FormSubmission $formSubmission): bool
    {
        if (! in_array($user->role, [UserRole::REPRESENTATIVE->value, UserRole::ADMIN->value], true)) {
            return false;
        }

        $formSubmission->loadMissing('batch');

        if ($formSubmission->status !== FormSubmissionStatus::FINALIZED) {
            return false;
        }

        if ($formSubmission->batch?->status !== BatchStatus::FINALIZED) {
            return false;
        }

        if ($formSubmission->batch?->application_status === ApplicationStatus::FOR_SUBMISSION) {
            return false;
        }

        return $formSubmission->flagged_by === null;
    }

    /**
     * Clear the revision flag (only the role that flagged may unflag, with batch/status guards).
     */
    public function unflagNeedsRevision(User $user, FormSubmission $formSubmission): bool
    {
        if (! in_array($user->role, [UserRole::REPRESENTATIVE->value, UserRole::ADMIN->value], true)) {
            return false;
        }

        if ($formSubmission->flagged_by !== $user->role) {
            return false;
        }

        $formSubmission->loadMissing('batch');

        if ($formSubmission->status !== FormSubmissionStatus::FINALIZED
            || $formSubmission->batch?->status !== BatchStatus::FINALIZED
            || $formSubmission->batch?->application_status === ApplicationStatus::FOR_SUBMISSION
            || $formSubmission->flagged_by === null) {
            return false;
        }

        if ($user->role === UserRole::REPRESENTATIVE->value
            && $formSubmission->batch?->application_status === ApplicationStatus::MODIFICATION_REQUESTED) {
            return false;
        }

        return true;
    }

    /**
     * Revert a finalized submission to pending (not allowed once the parent batch is finalized).
     */
    public function unfinalize(User $user, FormSubmission $formSubmission): bool
    {
        if ($formSubmission->status !== FormSubmissionStatus::FINALIZED) {
            return false;
        }

        if ($formSubmission->batch?->status === BatchStatus::FINALIZED) {
            return false;
        }

        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        if ($user->role === UserRole::REPRESENTATIVE->value) {
            return $formSubmission->office_id === $user->office_id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FormSubmission $formSubmission): bool
    {
        if ($formSubmission->batch?->application_status === ApplicationStatus::FOR_SUBMISSION) {
            return false;
        }

        if ($user->role === UserRole::ADMIN->value) {
            return true;
        }

        if ($user->role === UserRole::REPRESENTATIVE->value) {
            if ($formSubmission->office_id !== $user->office_id) {
                return false;
            }

            if ($formSubmission->status === FormSubmissionStatus::FINALIZED) {
                return false;
            }

            if ($formSubmission->status === FormSubmissionStatus::NEEDS_REVISION) {
                return $formSubmission->batch?->status === BatchStatus::NEEDS_REVISION;
            }

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
