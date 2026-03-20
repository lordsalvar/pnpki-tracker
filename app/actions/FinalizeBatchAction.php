<?php

namespace App\Actions;

use App\Enums\BatchStatus;
use App\Models\Batch;

class FinalizeBatchAction
{
    public function execute(Batch $batch): void
    {
        $batch->update([
            'status' => BatchStatus::FINALIZED->value,
        ]);
    }
}
