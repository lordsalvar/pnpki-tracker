<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Actions\FinalizeBatchAction;
use App\Enums\BatchStatus;
use App\Exports\FormSubmissionExport;
use App\Filament\Resources\Batches\BatchResource;
use App\Models\FormSubmission;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Maatwebsite\Excel\Facades\Excel;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Export CSV — only visible when batch is finalized
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->record->status === BatchStatus::FINALIZED->value)
                ->action(function () {
                    $batchName = str($this->record->batch_name)->slug();

                    $submissions = FormSubmission::with(['address', 'office'])
                        ->where('batch_id', $this->record->id)
                        ->get();

                    return Excel::download(
                        new FormSubmissionExport($submissions),
                        "batch-{$batchName}-" . now()->format('Y-m-d') . '.csv',
                        \Maatwebsite\Excel\Excel::CSV
                    );
                }),

            // Finalize — hidden when already finalized
            Action::make('finalize')
                ->label('Finalize Batch')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Finalize Batch')
                ->modalDescription('Once finalized, this batch can no longer be edited. Are you sure?')
                ->hidden(fn () => $this->record->status === BatchStatus::FINALIZED->value)
                ->action(function () {
                    app(FinalizeBatchAction::class)->execute($this->record);
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}