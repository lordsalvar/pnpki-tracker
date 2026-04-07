<?php

namespace App\Actions\Batch;

use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\User;
use App\Notifications\ModificationRequestedNotification;
use InvalidArgumentException;

class RequestModificationBatchAction
{
    /**
     * Representative requests admin review after flagged submissions on a finalized batch.
     *
     * @throws InvalidArgumentException
     */
    public function execute(Batch $batch): void
    {
        $batch->loadMissing('office');

        if ($batch->status !== BatchStatus::FINALIZED) {
            throw new InvalidArgumentException('The batch must be finalized before requesting modification.');
        }

        if (! $batch->formSubmissions()->whereNotNull('flagged_by')->exists()) {
            throw new InvalidArgumentException('There must be at least one flagged submission to request modification.');
        }

        if ($batch->application_status === ApplicationStatus::MODIFICATION_REQUESTED
            || $batch->application_status === ApplicationStatus::FOR_SUBMISSION) {
            throw new InvalidArgumentException('Modification cannot be requested in the current application status.');
        }

        $batch->update([
            'application_status' => ApplicationStatus::MODIFICATION_REQUESTED->value,
        ]);

        User::query()
            ->where('role', UserRole::ADMIN->value)
            ->get()
            ->each(fn (User $admin) => $admin->notify(new ModificationRequestedNotification($batch)));
    }
}
