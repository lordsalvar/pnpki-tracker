<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachment;

class EditFormSubmission extends EditRecord
{
    private const ATTACHMENT_FIELDS_BY_COMBO = [
        'national_id' => ['upload_pnpki', 'upload_national_id'],
        'passport_umid' => ['upload_pnpki', 'upload_passport', 'upload_umid'],
        'valid_ids' => ['upload_pnpki', 'upload_id1', 'upload_id2'],
    ];

    private const ATTACHMENT_TYPES_BY_FIELD = [
        'upload_pnpki' => ['PNPKI', 'upload_pnpki'],
        'upload_national_id' => ['NationalID', 'upload_national_id'],
        'upload_passport' => ['Passport', 'upload_passport'],
        'upload_umid' => ['UMID', 'upload_umid'],
        'upload_id1' => ['ID1', 'upload_id1'],
        'upload_id2' => ['ID2', 'upload_id2'],
    ];

    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            $data[$field] = $path;
        }

        $data['id_combo'] = $this->detectComboFromAttachmentPaths($attachmentPaths);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
        $this->syncAttachments($this->form->getRawState());
    }

    /**
     * Map existing attachment records to their corresponding form fields.
     */
    private function getAttachmentPathsByField(): array
    {
        $paths = [];

        foreach ($this->record->attachments as $attachment) {
            $field = $this->fieldFromFileType((string) $attachment->file_type);

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
        foreach (self::ATTACHMENT_FIELDS_BY_COMBO as $combo => $requiredFields) {
            $allFieldsPresent = collect($requiredFields)->every(fn (string $field): bool => ! empty($paths[$field] ?? null));

            if ($allFieldsPresent) {
                return $combo;
            }
        }

        return null;
    }

    /**
     * Synchronize attachment records and files based on active combo fields.
     */
    private function syncAttachments(array $rawData): void
    {
        $combo = $rawData['id_combo'] ?? null;
        $activeFields = self::ATTACHMENT_FIELDS_BY_COMBO[$combo] ?? [];
        $existingByField = $this->existingAttachmentsByField();
        $disk = Storage::disk('local');
        $targetDirectory = $this->attachmentDirectoryFor($rawData);
        $lastname = str($rawData['lastname'] ?? $this->record->lastname ?? 'Submission')->slug('_');
        $lastnameToken = (string) str($lastname === '' ? 'submission' : $lastname)->upper();

        foreach (array_keys(self::ATTACHMENT_TYPES_BY_FIELD) as $field) {
            $existingAttachment = $existingByField[$field] ?? null;

            if (! in_array($field, $activeFields, true)) {
                if ($existingAttachment) {
                    Storage::disk('local')->delete($existingAttachment->file_path);
                    $existingAttachment->delete();
                }

                continue;
            }

            $filePath = $this->normalizeFilePath($rawData[$field] ?? null);
            $canonicalFileType = self::ATTACHMENT_TYPES_BY_FIELD[$field][0];
            $resolvedFilePath = $this->resolveStoredPath($filePath, $disk);

            if ($resolvedFilePath === null) {
                $resolvedFilePath = $this->findCanonicalPathForType($disk, $targetDirectory, $lastnameToken, $canonicalFileType);
            }

            if ($existingAttachment) {
                $pathToCanonicalize = $resolvedFilePath;

                if ($pathToCanonicalize === null) {
                    $pathToCanonicalize = $existingAttachment->file_path;
                }

                $resolvedPath = $this->moveToCanonicalPath($pathToCanonicalize, $targetDirectory, $lastnameToken, $canonicalFileType);

                if ($existingAttachment->file_path !== $resolvedPath) {
                    Storage::disk('local')->delete($existingAttachment->file_path);
                }

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

            $filePath = $this->moveToCanonicalPath($resolvedFilePath, $targetDirectory, $lastnameToken, $canonicalFileType);

            Attachment::create([
                'form_submission_id' => $this->record->id,
                'file_type' => $canonicalFileType,
                'file_name' => basename($filePath),
                'file_path' => $filePath,
            ]);
        }
    }

    /**
     * Index existing attachments by upload field name.
     */
    private function existingAttachmentsByField(): array
    {
        $attachmentsByField = [];

        foreach ($this->record->attachments as $attachment) {
            $field = $this->fieldFromFileType((string) $attachment->file_type);

            if ($field === null) {
                continue;
            }

            $attachmentsByField[$field] = $attachment;
        }

        return $attachmentsByField;
    }

    /**
     * Resolve an upload field name from a stored attachment file type.
     */
    private function fieldFromFileType(string $fileType): ?string
    {
        foreach (self::ATTACHMENT_TYPES_BY_FIELD as $field => $types) {
            if (in_array($fileType, $types, true)) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Normalize FileUpload state into a single stored path string.
     */
    private function normalizeFilePath(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            $firstValue = array_values($value)[0] ?? null;

            return is_string($firstValue) ? $firstValue : null;
        }

        return is_string($value) ? $value : null;
    }

    /**
     * Resolve a stored file path, handling values that omit the attachments/ prefix.
     */
    private function resolveStoredPath(?string $path, FilesystemAdapter $disk): ?string
    {
        if ($path === null) {
            return null;
        }

        if ($disk->exists($path)) {
            return $path;
        }

        if (! str_starts_with($path, 'attachments/')) {
            $prefixedPath = "attachments/{$path}";

            if ($disk->exists($prefixedPath)) {
                return $prefixedPath;
            }
        }

        return null;
    }

    /**
     * Find a canonical file already placed in target directory for a specific type.
     */
    private function findCanonicalPathForType(FilesystemAdapter $disk, string $targetDirectory, string $lastnameToken, string $fileType): ?string
    {
        if (! $disk->exists($targetDirectory)) {
            return null;
        }

        $expectedPrefix = "{$targetDirectory}/{$lastnameToken}_{$fileType}.";

        foreach ($disk->files($targetDirectory) as $storedFile) {
            if (str_starts_with($storedFile, $expectedPrefix)) {
                return $storedFile;
            }
        }

        return null;
    }

    /**
     * Move a file into its canonical directory and filename pattern.
     */
    private function moveToCanonicalPath(string $sourcePath, string $targetDirectory, string $lastnameToken, string $fileType): string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? $extension : 'pdf';
        $targetPath = "{$targetDirectory}/{$lastnameToken}_{$fileType}.{$extension}";

        if ($sourcePath === $targetPath) {
            return $sourcePath;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($sourcePath)) {
            return $sourcePath;
        }

        $disk->makeDirectory($targetDirectory);

        if ($disk->exists($targetPath)) {
            $disk->delete($targetPath);
        }

        $disk->move($sourcePath, $targetPath);

        return $targetPath;
    }

    /**
     * Build the canonical attachment directory from office and name fields.
     */
    private function attachmentDirectoryFor(array $rawData): string
    {
        $officeId = $rawData['office_id'] ?? $this->record->office_id;
        $officeFolder = 'unknown-office';

        if ($officeId) {
            $office = \App\Models\Office::find($officeId);
            $officeFolder = $office ? str($office->acronym ?? $office->name)->slug() : 'unknown-office';
        }

        $firstname = str($rawData['firstname'] ?? $this->record->firstname ?? 'Unknown')->slug();
        $lastname = str($rawData['lastname'] ?? $this->record->lastname ?? 'Submission')->slug();
        $submissionFolder = "{$lastname}-{$firstname}";

        return "attachments/{$officeFolder}/FormSubmissions/{$submissionFolder}";
    }
}
