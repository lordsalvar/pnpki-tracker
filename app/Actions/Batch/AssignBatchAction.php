<?php

namespace App\Actions\Batch;

use App\Models\Batch;
use App\Models\FormSubmission;

class AssignBatchAction
{
    public function execute(FormSubmission $formSubmission, Batch $batch): void
    {
        $formSubmission->update(['batch_id' => $batch->id]);
    }
}
