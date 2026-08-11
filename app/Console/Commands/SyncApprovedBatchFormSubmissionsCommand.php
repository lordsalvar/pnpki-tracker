<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Enums\FormSubmissionStatus;
use App\Models\FormSubmission;
use Illuminate\Console\Command;

class SyncApprovedBatchFormSubmissionsCommand extends Command
{
    protected $signature = 'batches:sync-approved-submission-forms';

    protected $description = 'Set form submissions to Approved Submission when their batch is already Approved Submission';

    public function handle(): int
    {
        $updated = FormSubmission::query()
            ->where('status', '!=', FormSubmissionStatus::APPROVED_SUBMISSION->value)
            ->whereHas('batch', function ($query): void {
                $query->where('application_status', ApplicationStatus::APPROVED_SUBMISSION->value);
            })
            ->update([
                'status' => FormSubmissionStatus::APPROVED_SUBMISSION->value,
            ]);

        $this->info("Updated {$updated} form submission(s) to Approved Submission.");

        return self::SUCCESS;
    }
}
