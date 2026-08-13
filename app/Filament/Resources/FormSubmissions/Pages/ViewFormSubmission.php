<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Actions\FormSubmission\FlagNeedsRevisionFormSubmissionAction;
use App\Actions\FormSubmission\ForSubmissionAction;
use App\Actions\FormSubmission\UnFinalizeFormSubmissionAction;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\FormSubmissions\Concerns\HasBatchAssignmentActions;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use App\Models\User;
use App\Services\AttachmentRuleService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ViewFormSubmission extends ViewRecord
{
    use HasBatchAssignmentActions;

    protected static string $resource = FormSubmissionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->shouldShowPendingBatchReturnNotice()) {
            Notification::make()
                ->title('Batch not yet returned')
                ->body('You can resolve the flagged submission once the admin has reviewed and returned the batch.')
                ->warning()
                ->persistent()
                ->send();
        }

        if (($batchId = $this->getContextBatchId()) !== null) {
            session(['filament.last_viewed_batch_id' => $batchId]);
        }
    }

    public function getTitle(): string
    {
        $record = $this->record;

        return trim(
            $record->firstname.' '.
            (($record->middlename && $record->middlename !== 'N/A')
                ? strtoupper(substr($record->middlename, 0, 1)).'. '
                : '').
            $record->lastname.
            (($record->suffix && $record->suffix !== 'N/A')
                ? ', '.$record->suffix
                : '')
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_batch')
                ->label('Back to Batch')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->visible(fn (): bool => $this->getContextBatchId() !== null)
                ->url(fn (): string => BatchResource::getUrl('view', ['record' => $this->getContextBatchId()])),
            Action::make('edit_submission')
                ->label('Edit Submission')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn (): bool => $this->canSeeEditNeedsRevisionAction())
                ->url(fn (): string => FormSubmissionResource::getUrl('edit', array_filter([
                    'record' => $this->record,
                    'batch' => $this->getContextBatchId(),
                ], fn ($value): bool => filled($value)))),
            Action::make('for_submission')
                ->label('Mark as For Submission')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark submission as For Submission?')
                ->modalDescription('This will move the submission to the For Submission status.')
                ->visible(fn (): bool => Gate::allows('markForSubmission', $this->record))
                ->action(function (): void {
                    app(ForSubmissionAction::class)->execute($this->record);

                    $this->record->refresh();

                    Notification::make()
                        ->title('Submission marked as For Submission.')
                        ->success()
                        ->send();

                    if (Auth::user()?->role === UserRole::ADMIN->value) {
                        $batchId = $this->getContextBatchId() ?? $this->record->batch_id;

                        if ($batchId !== null) {
                            $this->redirect(BatchResource::getUrl('view', ['record' => $batchId]), navigate: true);
                        }
                    }
                }),
            Action::make('unfinalize')
                ->label('Revert to pending')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Revert submission to pending?')
                ->modalDescription('The submission will be editable again and can be finalized later. This is not available for submissions in a finalized batch.')
                ->visible(fn (): bool => Gate::allows('unfinalize', $this->record))
                ->action(function (): void {
                    Gate::authorize('unfinalize', $this->record);

                    app(UnFinalizeFormSubmissionAction::class)->execute($this->record);

                    $this->record->refresh();

                    Notification::make()
                        ->title('Submission reverted to pending.')
                        ->success()
                        ->send();

                    $this->redirect(FormSubmissionResource::getUrl('edit', ['record' => $this->record]));
                }),
            Action::make('flag_needs_revision')
                ->label('Flag Needs Revision')
                ->icon('heroicon-o-flag')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Flag Needs Revision')
                ->modalDescription('This will flag this submission for revision.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('flag_remarks')
                        ->label('Remarks')
                        ->placeholder('Explain what needs to be corrected...')
                        ->required(),
                ])
                ->visible(fn (): bool => $this->canSeeRevisionFlagActions()
                    && Gate::allows('flagNeedsRevision', $this->record))
                ->action(function (array $data): void {
                    $user = Auth::user();
                    abort_unless($user instanceof User, 403);

                    app(FlagNeedsRevisionFormSubmissionAction::class)->execute($this->record, $user, $data['flag_remarks']);

                    Notification::make()
                        ->title('Submission flagged for revision.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['flagged_by', 'flag_remarks']);

                    if (Auth::user()?->role === UserRole::ADMIN->value) {
                        $batchId = $this->getContextBatchId() ?? $this->record->batch_id;

                        if ($batchId !== null) {
                            $this->redirect(BatchResource::getUrl('view', ['record' => $batchId]), navigate: true);
                        }
                    }
                }),
            Action::make('unflag_needs_revision')
                ->label('Unflag Needs Revision')
                ->icon('heroicon-o-flag')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Unflag Needs Revision')
                ->modalDescription('This will remove the revision flag from this submission.')
                ->visible(fn (): bool => $this->canSeeRevisionFlagActions()
                    && Gate::allows('unflagNeedsRevision', $this->record))
                ->action(function (): void {
                    $user = Auth::user();
                    abort_unless($user instanceof User, 403);

                    Gate::forUser($user)->authorize('unflagNeedsRevision', $this->record);

                    $this->record->update([
                        'flagged_by' => null,
                        'flag_remarks' => null,
                    ]);

                    Notification::make()
                        ->title('Submission unflagged.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['flagged_by', 'flag_remarks']);
                }),
            $this->makeAssignBatchAction(),
            $this->makeUnassignBatchAction(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $address = Address::find($data['address_id']);

        if ($address) {
            $data['house_no'] = $address->house_no;
            $data['street'] = $address->street;
            $data['barangay'] = $address->barangay;
            $data['municipality'] = $address->municipality;
            $data['province'] = $address->province;
            $data['zip_code'] = $address->zip_code;
        }

        $attachmentPaths = $this->getAttachmentPathsByField();

        foreach ($attachmentPaths as $field => $path) {
            $data[$field] = [$path => $path];
        }

        $data['id_combo'] = $this->detectComboFromAttachmentPaths($attachmentPaths);

        return $data;
    }

    private function getAttachmentPathsByField(): array
    {
        $ruleService = app(AttachmentRuleService::class);
        $paths = [];

        foreach ($this->record->attachments as $attachment) {
            $field = $ruleService->fieldFromFileType((string) $attachment->file_type);

            if ($field === null) {
                continue;
            }

            $paths[$field] = $attachment->file_path;
        }

        return $paths;
    }

    private function detectComboFromAttachmentPaths(array $paths): ?string
    {
        return app(AttachmentRuleService::class)->detectComboFromPaths($paths);
    }

    /**
     * Representatives only see flag/unflag when the submission is finalized; admins follow policy alone.
     */
    private function canSeeRevisionFlagActions(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if ($user?->role !== UserRole::REPRESENTATIVE->value) {
            return true;
        }

        return $this->record->status === FormSubmissionStatus::FINALIZED;
    }

    private function shouldShowPendingBatchReturnNotice(): bool
    {
        $user = Auth::user();

        if ($user?->role !== UserRole::REPRESENTATIVE->value) {
            return false;
        }

        if ($this->record->status !== FormSubmissionStatus::NEEDS_REVISION) {
            return false;
        }

        if ($this->record->batch_id === null) {
            return false;
        }

        return $this->record->batch?->status !== BatchStatus::NEEDS_REVISION;
    }

    private function canSeeEditNeedsRevisionAction(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        if (! Gate::allows('update', $this->record)) {
            return false;
        }

        if ($this->record->status !== FormSubmissionStatus::NEEDS_REVISION) {
            return false;
        }

        if ($this->record->flagged_by === null) {
            return false;
        }

        if ($this->record->batch_id === null) {
            return true;
        }

        return $this->record->batch?->status === BatchStatus::NEEDS_REVISION;
    }

    private function getContextBatchId(): ?string
    {
        $batchId = request()->query('batch');

        if (! is_string($batchId) || $batchId === '') {
            return null;
        }

        if ((string) $this->record->batch_id !== $batchId) {
            return null;
        }

        return $batchId;
    }
}
