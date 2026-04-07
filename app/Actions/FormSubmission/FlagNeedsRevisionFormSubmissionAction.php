<?php

namespace App\Actions\FormSubmission;

use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class FlagNeedsRevisionFormSubmissionAction
{
    public function execute(FormSubmission $formSubmission, User $user): void
    {
        Gate::forUser($user)->authorize('flagNeedsRevision', $formSubmission);

        $formSubmission->update([
            'flagged_by' => $user->role,
        ]);
    }
}
