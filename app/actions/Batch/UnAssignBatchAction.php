<?php

namespace App\Actions\Batch;

use App\Models\FormSubmission;

class UnAssignBatchAction
{
    public function execute(FormSubmission $formSubmission): void
    {
        $formSubmission->update(['batch_id' => null]);
    }
}
