<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Models\Batch;
use Filament\Notifications\Notification;

class FinalizeBatchAction
{
    public function execute(Batch $batch): void
    {
        if ($batch->formSubmissions->count() === 0) {
            Notification::make()
                ->title('Batch has no submissions. Please add submissions to the batch before finalizing.')
                ->warning()
                ->send();

            return;
        } else {
            $batch->update([
                'status' => BatchStatus::FINALIZED->value,
                'application_status' => ApplicationStatus::PENDING_FOR_REVIEW->value,
            ]);

            Notification::make()
                ->title('Batch finalized successfully.')
                ->success()
                ->send();
        }
    }
}
