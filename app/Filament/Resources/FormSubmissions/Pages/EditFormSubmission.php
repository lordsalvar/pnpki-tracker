<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Actions\Batch\AssignBatchAction;
use App\Actions\Batch\UnAssignBatchAction;
use App\Actions\FormSubmission\FinalizeFormSubmissionAction;
use App\Enums\FormSubmissionStatus;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use App\Models\Attachment;
use App\Models\Batch;
use App\Services\AttachmentPathService;
use App\Services\AttachmentRuleService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use App\Enums\BatchStatus;

class EditFormSubmission extends EditRecord
{
    private ?string $originalFirstname = null;

    private ?string $originalLastname = null;

    protected static string $resource = FormSubmissionResource::class;

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
            Action::make('finalize')
                ->label('Finalize')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->hidden(fn () => $this->record->status === FormSubmissionStatus::FINALIZED)
                ->action(function () {
                    app(FinalizeFormSubmissionAction::class)->execute($this->record);

                    Notification::make()
                        ->title('Submission finalized.')
                        ->success()
                        ->send();

                    $this->refreshFormWithPersistedState();
                }),
            Action::make('assign_batch')
                ->label('Assign to Batch')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('info')
                ->visible(fn () => $this->record->status === FormSubmissionStatus::FINALIZED && $this->record->batch_id === null)
                ->schema([
                    Select::make('batch_id')
                        ->label('Batch')
                        ->options(
                            Batch::query()
                                ->where('status', '!=', BatchStatus::FINALIZED)
                                ->orderBy('batch_name')
                                ->pluck('batch_name', 'id')
                        )
                        ->required()
                        ->searchable()
                        ->default(fn () => $this->record->batch_id),
                ])
                ->action(function (array $data) {
                    $batch = Batch::findOrFail($data['batch_id']);

                    if ($batch->status === BatchStatus::FINALIZED) {
                        Notification::make()
                            ->title('Cannot assign to a finalized batch.')
                            ->danger()
                            ->send();

                        return;
                    }

                    app(AssignBatchAction::class)->execute($this->record, $batch);

                    Notification::make()
                        ->title('Batch assigned.')
                        ->success()
                        ->send();

                    $this->refreshFormWithPersistedState();
                }),

            Action::make('unassign_batch')
                ->label('Remove from Batch')
                ->icon('heroicon-o-archive-box-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record->batch_id !== null)
                ->requiresConfirmation()
                ->action(function () {
                    app(UnAssignBatchAction::class)->execute($this->record);

                    Notification::make()
                        ->title('Batch unassigned.')
                        ->success()
                        ->send();

                    $this->refreshFormWithPersistedState();
                }),
            DeleteAction::make(),
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->originalFirstname = $this->record->firstname;
        $this->originalLastname = $this->record->lastname;
        if ($this->record->address) {
            $this->record->address->update([
                'house_no' => $data['house_no'],
                'street' => $data['street'],
                'barangay' => $data['barangay'],
                'municipality' => $data['municipality'],
                'province' => $data['province'],
                'zip_code' => $data['zip_code'],
            ]);
        }

        unset($data['house_no'], $data['street'], $data['barangay'], $data['municipality'], $data['province'], $data['zip_code']);

        return $data;
    }

    protected function afterSave(): void
    {
        $rawData = $this->form->getRawState();

        if (! $this->validateAttachmentsForCombo($rawData)) {
            return;
        }

        try {
            $this->syncAttachments($rawData);
            $this->refreshFormWithPersistedState();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('File sync failed.')
                ->body('Your record was saved but attachments could not be updated. Please try again or contact support.')
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('EditFormSubmission::syncAttachments failed', [
                'submission_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map existing attachment records to their corresponding form fields.
     */
    private function getAttachmentPathsByField(): array
    {
        $ruleService = $this->ruleService();
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

    /**
     * Infer the ID combo from currently attached file fields.
     */
    private function detectComboFromAttachmentPaths(array $paths): ?string
    {
        return $this->ruleService()->detectComboFromPaths($paths);
    }

    /**
     * Synchronize attachment records and files based on active combo fields.
     */
    private function syncAttachments(array $rawData): void
    {
        $ruleService = $this->ruleService();
        $pathService = $this->pathService();
        $activeFields = $ruleService->activeFieldsForCombo($rawData['id_combo'] ?? null);
        $existingByField = $this->existingAttachmentsByField();
        $disk = Storage::disk('local');
        $targetDirectory = $this->attachmentDirectoryFor($rawData);
        $lastnameToken = $pathService->lastnameToken((string) ($rawData['lastname'] ?? $this->record->lastname ?? 'Submission'), 'submission');

        foreach ($ruleService->allFields() as $field) {
            $existingAttachment = $existingByField[$field] ?? null;

            if (! in_array($field, $activeFields, true)) {
                if ($existingAttachment) {
                    $disk->delete($existingAttachment->file_path);
                    $existingAttachment->delete();
                }

                continue;
            }

            $filePath = $pathService->normalizeFilePath($rawData[$field] ?? null);
            $canonicalFileType = $ruleService->fileTypeForField($field);

            if ($canonicalFileType === null) {
                continue;
            }

            $resolvedFilePath = $pathService->resolveStoredPath($filePath, $disk);

            if ($resolvedFilePath === null) {
                $resolvedFilePath = $pathService->findCanonicalPathForType($disk, $targetDirectory, $lastnameToken, $canonicalFileType);
            }

            if ($existingAttachment) {
                $pathToCanonicalize = $resolvedFilePath;

                if ($pathToCanonicalize === null) {
                    $pathToCanonicalize = $existingAttachment->file_path;
                }

                $resolvedPath = $pathService->moveToCanonicalPath($disk, $pathToCanonicalize, $targetDirectory, $lastnameToken, $canonicalFileType);

                $existingAttachment->update([
                    'file_type' => $canonicalFileType,
                    'file_name' => basename($resolvedPath),
                    'file_path' => $resolvedPath,
                ]);

                continue;
            }

            if ($resolvedFilePath === null) {
                continue;
            }

            $filePath = $pathService->moveToCanonicalPath($disk, $resolvedFilePath, $targetDirectory, $lastnameToken, $canonicalFileType);

            Attachment::create([
                'form_submission_id' => $this->record->id,
                'file_type' => $canonicalFileType,
                'file_name' => basename($filePath),
                'file_path' => $filePath,
            ]);
        }
        $this->cleanupOldDirectory($rawData);
    }

    /**
     * Index existing attachments by upload field name.
     */
    private function existingAttachmentsByField(): array
    {
        $ruleService = $this->ruleService();
        $attachmentsByField = [];

        foreach ($this->record->attachments as $attachment) {
            $field = $ruleService->fieldFromFileType((string) $attachment->file_type);

            if ($field === null) {
                continue;
            }

            $attachmentsByField[$field] = $attachment;
        }

        return $attachmentsByField;
    }

    /**
     * Build the canonical attachment directory from office and name fields.
     */
    private function attachmentDirectoryFor(array $rawData): string
    {
        $officeId = $rawData['office_id'] ?? $this->record->office_id;
        $officeFolder = 'unknown-office';

        if ($officeId) {
            // Use already-loaded relationship if available to avoid extra queries.
            $office = ($this->record->office_id === $officeId)
                ? $this->record->office
                : \App\Models\Office::find($officeId);

            $officeFolder = $office
                ? str($office->acronym ?? $office->name)->slug()
                : 'unknown-office';
        }

        return $this->pathService()->submissionDirectory(
            $officeFolder,
            (string) ($rawData['firstname'] ?? $this->record->firstname ?? 'Unknown'),
            (string) ($rawData['lastname'] ?? $this->record->lastname ?? 'Submission'),
            $this->record->id,
        );
    }

    private function validateAttachmentsForCombo(array $rawData): bool
    {
        $ruleService = $this->ruleService();
        $pathService = $this->pathService();
        $activeFields = $ruleService->activeFieldsForCombo($rawData['id_combo'] ?? null);

        if (empty($activeFields)) {
            Notification::make()
                ->title('No ID combination selected.')
                ->danger()
                ->send();

            return false;
        }

        $disk = Storage::disk('local');
        $targetDir = $this->attachmentDirectoryFor($rawData);
        $lastnameToken = $pathService->lastnameToken((string) ($rawData['lastname'] ?? $this->record->lastname ?? 'Submission'), 'submission');

        $missing = [];

        foreach ($activeFields as $field) {
            $resolved = $pathService->resolveStoredPath(
                $pathService->normalizeFilePath($rawData[$field] ?? null),
                $disk
            );

            if ($resolved === null) {
                $canonicalType = $ruleService->fileTypeForField($field);
                $resolved = $canonicalType === null
                    ? null
                    : $pathService->findCanonicalPathForType($disk, $targetDir, $lastnameToken, $canonicalType);
            }

            if ($resolved === null) {
                $missing[] = $ruleService->humanLabelForField($field);
            }
        }

        if (! empty($missing)) {
            Notification::make()
                ->title('Missing required attachments.')
                ->body('Please upload: '.implode(', ', $missing).'.')
                ->danger()
                ->persistent()
                ->send();

            return false;
        }

        return true;
    }

    /**
     * Delete the old attachment directory if the name changed and it is now empty
     * or only contains stale files that no longer belong to this record.
     */
    private function cleanupOldDirectory(array $rawData): void
    {
        $disk = Storage::disk('local');
        $currentDirectory = $this->attachmentDirectoryFor($rawData);

        // Reconstruct old directory using pre-save name values.
        $oldDirectory = $this->attachmentDirectoryFor([
            'office_id' => $rawData['office_id'] ?? $this->record->office_id,
            'firstname' => $this->originalFirstname ?? $this->record->firstname ?? 'Unknown',
            'lastname' => $this->originalLastname ?? $this->record->lastname ?? 'Submission',
        ]);

        if ($oldDirectory === $currentDirectory) {
            return;
        }

        if (! $disk->exists($oldDirectory)) {
            return;
        }

        foreach ($disk->files($oldDirectory) as $strayFile) {
            $disk->delete($strayFile);
        }

        if (empty($disk->files($oldDirectory))) {
            $disk->deleteDirectory($oldDirectory);
        }
    }

    private function refreshFormWithPersistedState(): void
    {
        $this->record->refresh();
        $data = $this->mutateFormDataBeforeFill($this->record->attributesToArray());
        $this->form->fill($data);
    }

    private function ruleService(): AttachmentRuleService
    {
        return app(AttachmentRuleService::class);
    }

    private function pathService(): AttachmentPathService
    {
        return app(AttachmentPathService::class);
    }
}
