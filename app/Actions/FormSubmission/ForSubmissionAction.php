<?php

namespace App\Actions\FormSubmission;

use App\Enums\FormSubmissionStatus;
use App\Models\FormSubmission;

class ForSubmissionAction
{
    public function execute(FormSubmission $formSubmission): void
    {
        $formSubmission->update([
            'status' => FormSubmissionStatus::FOR_SUBMISSION->value,
            'flagged_by' => null,
            'flag_remarks' => null,
        ]);
    }
}
