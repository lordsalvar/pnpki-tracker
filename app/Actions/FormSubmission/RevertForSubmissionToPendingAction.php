<?php

namespace App\Actions\FormSubmission;

use App\Enums\ApplicationStatus;
use App\Enums\FormSubmissionStatus;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class RevertForSubmissionToPendingAction
{
    /**
     * Revert a for-submission record to pending and remove it from its batch.
     * If the parent batch was marked For Submission, its application status returns to Pending for Review.
     *
     * @throws InvalidArgumentException
     */
    public function execute(FormSubmission $formSubmission): void
    {
        Gate::authorize('revertForSubmissionToPending', $formSubmission);

        $formSubmission->loadMissing('batch');

        if ($formSubmission->status !== FormSubmissionStatus::FOR_SUBMISSION) {
            throw new InvalidArgumentException('Only for-submission records can be reverted to pending.');
        }

        $batch = $formSubmission->batch;

        $formSubmission->update([
            'status' => FormSubmissionStatus::PENDING->value,
            'batch_id' => null,
            'flagged_by' => null,
            'flag_remarks' => null,
        ]);

        if ($batch !== null
            && $batch->application_status === ApplicationStatus::FOR_SUBMISSION) {
            $batch->update([
                'application_status' => ApplicationStatus::PENDING_FOR_REVIEW->value,
            ]);
        }
    }
}
