<?php

namespace App\Actions\Batch;

use App\Enums\ApplicationStatus;
use App\Enums\FormSubmissionStatus;
use App\Models\Batch;
use InvalidArgumentException;

class ApproveBatchSubmissionAction
{
    public function execute(Batch $batch): void
    {
        if ($batch->application_status !== ApplicationStatus::FOR_SUBMISSION) {
            throw new InvalidArgumentException('Only batches marked For Submission can be approved.');
        }

        $batch->update([
            'application_status' => ApplicationStatus::APPROVED_SUBMISSION->value,
        ]);

        $batch->formSubmissions()->update([
            'status' => FormSubmissionStatus::APPROVED_SUBMISSION->value,
        ]);
    }
}
