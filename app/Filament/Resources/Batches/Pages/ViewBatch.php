<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Actions\FinalizeBatchAction;
use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\BatchResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->batch_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Finalize — hidden when already finalized
            Action::make('finalize')
                ->label('Finalize Batch')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Finalize Batch')
                ->modalDescription('Once finalized, this batch can no longer be edited. Are you sure?')
                ->hidden(fn () => $this->record->status === BatchStatus::FINALIZED
                        || Auth::user()?->role !== UserRole::REPRESENTATIVE->value)
                ->action(function () {
                    app(FinalizeBatchAction::class)->execute($this->record);
                    $this->refreshFormData(['status']);
                }),
            Action::make('request_modification')
                ->label('Request Modification')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Request Modification')
                ->modalDescription('This will mark the application as Modification Requested.')
                ->visible(fn () => Auth::user()?->role === UserRole::REPRESENTATIVE->value
                    && $this->record->status === BatchStatus::FINALIZED
                    && $this->record->formSubmissions()->where('flagged_by_representative', true)->exists()
                    && $this->record->application_status !== ApplicationStatus::MODIFICATION_REQUESTED)
                ->action(function () {
                    $this->record->update([
                        'application_status' => ApplicationStatus::MODIFICATION_REQUESTED->value,
                    ]);

                    $this->refreshFormData(['application_status']);

                    Notification::make()
                        ->title('Application status updated to Modification Requested.')
                        ->success()
                        ->send();
                }),
            Action::make('accept_modification_request')
                ->label('Accept Modification Request')
                ->icon('heroicon-o-check-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Accept Modification Request')
                ->modalDescription('This will move this batch to Needs Revision and update flagged submissions.')
                ->visible(fn () => Auth::user()?->role === UserRole::ADMIN->value
                    && $this->record->application_status === ApplicationStatus::MODIFICATION_REQUESTED)
                ->action(function () {
                    $this->record->update([
                        'application_status' => null,
                        'status' => BatchStatus::NEEDS_REVISION->value,
                    ]);

                    $this->record->formSubmissions()
                        ->where('flagged_by_representative', true)
                        ->update([
                            'status' => FormSubmissionStatus::NEEDS_REVISION->value,
                            'flagged_by_representative' => false,
                        ]);

                    $this->refreshFormData(['application_status', 'status']);

                    Notification::make()
                        ->title('Modification request accepted. Batch moved to Needs Revision.')
                        ->success()
                        ->send();
                }),

            Action::make('return_for_revision')
                ->label('Return Batch for Revision')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Return Batch for Revision')
                ->modalDescription('This will set the application status to Needs Revision and move flagged submissions to Needs Revision.')
                ->visible(fn () => Auth::user()?->role === UserRole::ADMIN->value
                    && $this->record->status === BatchStatus::FINALIZED
                    && $this->record->formSubmissions()
                        ->where('flagged_by_representative', true)
                        ->exists())
                ->action(function () {
                    $this->record->update([
                        'status' => BatchStatus::NEEDS_REVISION->value,
                        'application_status' => ApplicationStatus::NEEDS_REVISION->value,
                    ]);

                    $this->record->formSubmissions()
                        ->where('flagged_by_representative', true)
                        ->update([
                            'status' => FormSubmissionStatus::NEEDS_REVISION->value,
                            'flagged_by_representative' => false,
                        ]);

                    Notification::make()
                        ->title('Batch returned to representative for revision.')
                        ->success()
                        ->send();

                    $this->redirect(BatchResource::getUrl('index'));
                }),
        ];
    }
}
