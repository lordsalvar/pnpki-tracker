<?php

namespace App\Actions\FormSubmission;

use App\Enums\FormSubmissionStatus;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Gate;

class ForSubmissionAction
{
    public function execute(FormSubmission $formSubmission): void
    {
        Gate::authorize('markForSubmission', $formSubmission);

        $formSubmission->update([
            'status' => FormSubmissionStatus::FOR_SUBMISSION->value,
            'flagged_by' => null,
            'flag_remarks' => null,
        ]);
    }
}
