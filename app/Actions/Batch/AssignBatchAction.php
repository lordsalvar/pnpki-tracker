<?php

namespace App\Actions\Batch;

use App\Enums\BatchStatus;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\FormSubmission;
use App\Models\User;
use InvalidArgumentException;

class AssignBatchAction
{
    public function execute(FormSubmission $formSubmission, Batch $batch, ?User $actor = null): void
    {
        if ($batch->office_id !== $formSubmission->office_id) {
            throw new InvalidArgumentException('The batch must belong to the same office as the submission.');
        }

        $actor ??= auth()->user();

        if ($actor?->role !== UserRole::ADMIN->value && $batch->status === BatchStatus::FINALIZED) {
            throw new InvalidArgumentException('Cannot assign to a finalized batch.');
        }

        $formSubmission->update(['batch_id' => $batch->id]);
    }
}
