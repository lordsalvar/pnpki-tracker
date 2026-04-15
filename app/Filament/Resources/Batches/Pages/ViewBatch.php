<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Actions\Batch\AcceptModificationRequestBatchAction;
use App\Actions\Batch\DownloadBatchAttachmentsAction;
use App\Actions\Batch\RequestModificationBatchAction;
use App\Actions\FinalizeBatchAction;
use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\BatchResource;
use App\Notifications\BatchNeedsRevisionNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $currentBatchId = (string) $this->record->getKey();

        if (request()->boolean('from_nav')) {
            $lastNavBatchId = (string) session('filament.batches_nav_last_batch_id', '');
            $navClicks = (int) session('filament.batches_nav_clicks', 0);

            $navClicks = $lastNavBatchId === $currentBatchId ? $navClicks + 1 : 1;

            session([
                'filament.batches_nav_last_batch_id' => $currentBatchId,
                'filament.batches_nav_clicks' => $navClicks,
            ]);

            if ($navClicks >= 2) {
                session()->forget([
                    'filament.last_viewed_batch_id',
                    'filament.batches_nav_last_batch_id',
                    'filament.batches_nav_clicks',
                ]);

                $this->redirect(BatchResource::getUrl('index'), navigate: true);

                return;
            }
        } else {
            session()->forget([
                'filament.batches_nav_last_batch_id',
                'filament.batches_nav_clicks',
            ]);
        }

        session(['filament.last_viewed_batch_id' => (string) $this->record->getKey()]);
    }

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
                ->disabled(fn () => $this->hasNeedsRevisionSubmissions())
                ->tooltip(function (): ?string {
                    if ($this->hasNeedsRevisionSubmissions()) {
                        return 'Resolve all submissions marked as Needs Revision before finalizing this batch.';
                    }

                    return null;
                })
                ->hidden(fn () => $this->record->status === BatchStatus::FINALIZED
                        || Auth::user()?->role !== UserRole::REPRESENTATIVE->value)
                ->action(function () {
                    if ($this->hasNeedsRevisionSubmissions()) {
                        Notification::make()
                            ->title('You cannot finalize while there are submissions that need revision')
                            ->warning()
                            ->send();

                        return;
                    }

                    app(FinalizeBatchAction::class)->execute($this->record);
                    $admins = \App\Models\User::where('role', UserRole::ADMIN->value)->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\BatchFinalizedNotification($this->record));
                    }
                    $this->refreshFormData(['status']);
                }),
            Action::make('mark_for_submission')
                ->label('Mark as For Submission')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark as For Submission')
                ->modalDescription('Every submission in this batch must already be marked For Submission. This updates the batch application status.')
                ->visible(fn () => Auth::user()?->role === UserRole::ADMIN->value
                    && $this->isBatchFinalized()
                    && ! $this->isForSubmission())
                ->disabled(fn () => $this->hasSubmissionsNotMarkedForSubmission() || $this->hasFlaggedSubmissions())
                ->tooltip(function (): ?string {
                    if ($this->hasSubmissionsNotMarkedForSubmission()) {
                        return 'All submissions must be marked For Submission before marking the batch';
                    }

                    if ($this->hasFlaggedSubmissions()) {
                        return 'Some submissions are still flagged for revision';
                    }

                    return null;
                })
                ->action(function () {
                    $notAllForSubmission = $this->record->formSubmissions()
                        ->where('status', '!=', FormSubmissionStatus::FOR_SUBMISSION->value)
                        ->exists();

                    if ($notAllForSubmission) {
                        Notification::make()
                            ->title('All submissions must be marked For Submission before marking the batch')
                            ->warning()
                            ->send();

                        return;
                    }

                    $hasFlagged = $this->record->formSubmissions()
                        ->whereNotNull('flagged_by')
                        ->exists();

                    if ($hasFlagged) {
                        Notification::make()
                            ->title('All flagged submissions must be resolved before marking for submission')
                            ->warning()
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'application_status' => ApplicationStatus::FOR_SUBMISSION->value,
                    ]);

                    $this->refreshFormData(['application_status']);

                    Notification::make()
                        ->title('Batch marked as For Submission.')
                        ->success()
                        ->send();
                }),
            Action::make('request_modification')
                ->label('Request Modification')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Request Modification')
                ->modalDescription('This will mark the application as Modification Requested and notify administrators.')
                ->visible(fn () => Auth::user()?->role === UserRole::REPRESENTATIVE->value
                    && $this->record->status === BatchStatus::FINALIZED
                    && $this->record->application_status !== ApplicationStatus::MODIFICATION_REQUESTED
                    && ! $this->isForSubmission())
                ->disabled(fn () => ! $this->hasFlaggedSubmissions())
                ->tooltip(function (): ?string {
                    if (! $this->hasFlaggedSubmissions()) {
                        return 'Flag at least one submission for revision before requesting modification.';
                    }

                    return null;
                })
                ->action(function () {
                    try {
                        app(RequestModificationBatchAction::class)->execute($this->record);
                    } catch (InvalidArgumentException $exception) {
                        Notification::make()
                            ->title($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

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
                    && $this->record->application_status === ApplicationStatus::MODIFICATION_REQUESTED
                    && $this->record->application_status !== ApplicationStatus::FOR_SUBMISSION)
                ->action(function () {
                    try {
                        app(AcceptModificationRequestBatchAction::class)->execute($this->record);
                    } catch (InvalidArgumentException $exception) {
                        Notification::make()
                            ->title($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

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
                        ->where('flagged_by', UserRole::ADMIN->value)
                        ->exists())
                ->action(function () {
                    $this->record->update([
                        'status' => BatchStatus::NEEDS_REVISION->value,
                        'application_status' => ApplicationStatus::NEEDS_REVISION->value,
                    ]);

                    $this->record->user?->notify(new BatchNeedsRevisionNotification($this->record));

                    $this->record->formSubmissions()
                        ->where('flagged_by', UserRole::ADMIN->value)
                        ->update([
                            'status' => FormSubmissionStatus::NEEDS_REVISION->value,
                        ]);

                    Notification::make()
                        ->title('Batch returned to representative for revision.')
                        ->success()
                        ->send();

                    $this->redirect(BatchResource::getUrl('index'));
                }),
            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => Auth::user()?->role === UserRole::ADMIN->value
                    && $this->isForSubmission())
                ->url(fn () => route('batch.export-csv', $this->record->id))
                ->openUrlInNewTab(),
            Action::make('download_attachments')
                ->label('Download Attachments')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->visible(fn () => Auth::user()?->role === UserRole::ADMIN->value
                    && $this->isForSubmission())
                ->action(function () {
                    return app(DownloadBatchAttachmentsAction::class)->execute($this->record);
                }),

        ];
    }

    private function hasSubmissionsNotMarkedForSubmission(): bool
    {
        return $this->record->formSubmissions()
            ->where('status', '!=', FormSubmissionStatus::FOR_SUBMISSION->value)
            ->exists();
    }

    private function hasNeedsRevisionSubmissions(): bool
    {
        return $this->record->formSubmissions()
            ->where('status', FormSubmissionStatus::NEEDS_REVISION->value)
            ->exists();
    }

    private function hasFlaggedSubmissions(): bool
    {
        return $this->record->formSubmissions()
            ->whereNotNull('flagged_by')
            ->exists();
    }

    private function isBatchFinalized(): bool
    {
        return in_array($this->record->status, [BatchStatus::FINALIZED, BatchStatus::FINALIZED->value], true);
    }

    private function isForSubmission(): bool
    {
        return in_array($this->record->application_status, [ApplicationStatus::FOR_SUBMISSION, ApplicationStatus::FOR_SUBMISSION->value], true);
    }
}
