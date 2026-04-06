<?php

namespace App\Actions\FormSubmission;

use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Models\FormSubmission;
use InvalidArgumentException;

class UnFinalizeFormSubmissionAction
{
    /**
     * Revert a finalized submission to pending. Blocked when the submission is in a finalized batch.
     *
     * @throws InvalidArgumentException
     */
    public function execute(FormSubmission $formSubmission): void
    {
        $formSubmission->loadMissing('batch');

        if ($formSubmission->status !== FormSubmissionStatus::FINALIZED) {
            throw new InvalidArgumentException('Only finalized submissions can be reverted to pending.');
        }

        if ($formSubmission->batch_id !== null
            && $formSubmission->batch?->status === BatchStatus::FINALIZED) {
            throw new InvalidArgumentException('Cannot revert a submission that belongs to a finalized batch.');
        }

        $formSubmission->update([
            'status' => FormSubmissionStatus::PENDING->value,
        ]);
    }
}
