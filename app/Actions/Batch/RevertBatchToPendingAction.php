<?php

namespace App\Actions\Batch;

use App\Enums\BatchStatus;
use App\Models\Batch;
use InvalidArgumentException;

class RevertBatchToPendingAction
{
    /**
     * Revert a finalized batch to pending so the representative can edit it again.
     *
     * @throws InvalidArgumentException
     */
    public function execute(Batch $batch): void
    {
        if ($batch->status !== BatchStatus::FINALIZED) {
            throw new InvalidArgumentException('Only finalized batches can be reverted to pending.');
        }

        $batch->update([
            'status' => BatchStatus::PENDING->value,
            'application_status' => null,
        ]);
    }
}
