<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Models\Batch;

class FinalizeBatchAction
{
    public function execute(Batch $batch): void
    {
        $batch->update([
            'status' => BatchStatus::FINALIZED->value,
            'application_status' => ApplicationStatus::PENDING_FOR_REVIEW->value,
        ]);
    }
}
