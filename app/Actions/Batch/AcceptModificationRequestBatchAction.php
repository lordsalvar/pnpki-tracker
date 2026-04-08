<?php

namespace App\Actions\Batch;

use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Notifications\BatchNeedsRevisionNotification;
use InvalidArgumentException;

class AcceptModificationRequestBatchAction
{
    /**
     * Admin accepts the modification request: batch moves to Needs Revision and rep-flagged submissions follow.
     *
     * @throws InvalidArgumentException
     */
    public function execute(Batch $batch): void
    {
        $batch->loadMissing('user');

        if ($batch->application_status !== ApplicationStatus::MODIFICATION_REQUESTED) {
            throw new InvalidArgumentException('The batch is not in modification requested status.');
        }

        $batch->update([
            'application_status' => null,
            'status' => BatchStatus::NEEDS_REVISION->value,
        ]);

        $batch->user?->notify(new BatchNeedsRevisionNotification($batch));

        $batch->formSubmissions()
            ->where('flagged_by', UserRole::REPRESENTATIVE->value)
            ->update([
                'status' => FormSubmissionStatus::NEEDS_REVISION->value,
            ]);
    }
}
