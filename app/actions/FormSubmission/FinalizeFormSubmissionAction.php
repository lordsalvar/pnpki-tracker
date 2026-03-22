<?php

namespace App\Actions\FormSubmission;

use App\Enums\FormSubmissionStatus;
use App\Models\FormSubmission;

class FinalizeFormSubmissionAction
{
    public function execute(FormSubmission $formSubmission): void
    {
        $formSubmission->update([
            'status' => FormSubmissionStatus::FINALIZED->value,
        ]);
    }
}
