<?php

namespace App\Actions\FormSubmission;

use App\Models\Address;
use App\Models\Attachment;
use App\Models\EmployeeForm;
use App\Models\FormSubmission;
use App\Models\User;
use App\Services\AttachmentPathService;
use App\Services\AttachmentRuleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StorePublicFormSubmissionAction
{
    public function __construct(
        private AttachmentRuleService $ruleService,
        private AttachmentPathService $pathService,
    ) {}

    /**
     * Persist address, form submission, and attachment rows. Normalizes upload paths inside the transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(EmployeeForm $employeeForm, User $representative, array $data): FormSubmission
    {
        $data = $this->normalizeUploadedAttachmentPaths($employeeForm, $data);

        return DB::transaction(function () use ($employeeForm, $representative, $data): FormSubmission {
            $address = Address::create([
                'house_no' => $data['house_no'],
                'street' => $data['street'],
                'barangay' => $data['barangay'],
                'municipality' => $data['municipality'],
                'province' => $data['province'],
                'zip_code' => $data['zip_code'],
            ]);

            $formSubmission = FormSubmission::create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'middlename' => $data['middlename'],
                'suffix' => $data['suffix'],
                'maiden_name' => $data['maiden_name'] ?? null,
                'birth_date' => $data['birth_date'],
                'birth_place_country' => $data['birth_place_country'],
                'birth_place_province' => $data['birth_place_province'],
                'civil_status' => $data['civil_status'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'organization' => $data['organization'] ?? null,
                'organizational_unit' => $data['organizational_unit'],
                'sex' => $data['sex'],
                'tin_number' => $data['tin_number'],
                'address_id' => $address->id,
                'office_id' => $representative->office_id,
                'form_id' => $employeeForm->id,
                'status' => 'pending',
            ]);

            $this->saveAttachments($formSubmission, $data);

            return $formSubmission;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveAttachments(FormSubmission $formSubmission, array $data): void
    {
        $fileKeys = $this->ruleService->activeFieldsForCombo($data['id_combo'] ?? null);

        foreach ($this->ruleService->allFields() as $field) {
            if (! in_array($field, $fileKeys, true)) {
                continue;
            }

            $type = $this->ruleService->fileTypeForField($field);

            if ($type === null) {
                continue;
            }

            if (! empty($data[$field])) {
                $path = is_array($data[$field]) ? array_values($data[$field])[0] : $data[$field];

                Attachment::create([
                    'form_submission_id' => $formSubmission->id,
                    'file_type' => $type,
                    'file_name' => basename($path),
                    'file_path' => $path,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeUploadedAttachmentPaths(EmployeeForm $employeeForm, array $data): array
    {
        $activeFields = $this->ruleService->activeFieldsForCombo($data['id_combo'] ?? null);

        if ($activeFields === []) {
            return $data;
        }

        $disk = Storage::disk('local');
        $targetDirectory = $this->attachmentDirectoryFor($employeeForm, $data);
        $lastnameToken = $this->pathService->lastnameToken((string) ($data['lastname'] ?? 'Employee'), 'employee');

        foreach ($activeFields as $field) {
            $sourcePath = $this->pathService->normalizeFilePath($data[$field] ?? null);

            if (! is_string($sourcePath) || $sourcePath === '') {
                continue;
            }

            $fileType = $this->ruleService->fileTypeForField($field) ?? 'FILE';
            $targetPath = $this->pathService->canonicalPath($sourcePath, $targetDirectory, $lastnameToken, $fileType);

            if ($sourcePath !== $targetPath && $disk->exists($sourcePath)) {
                $disk->makeDirectory($targetDirectory);

                if ($disk->exists($targetPath)) {
                    $disk->delete($targetPath);
                }

                $disk->move($sourcePath, $targetPath);
            }

            $data[$field] = is_array($data[$field]) ? [$targetPath] : $targetPath;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function attachmentDirectoryFor(EmployeeForm $employeeForm, array $data): string
    {
        $office = $employeeForm->user?->office;
        $officeFolder = $office
            ? str($office->acronym ?? $office->name)->slug()
            : 'unknown-office';

        return $this->pathService->employeeDirectory(
            $officeFolder,
            (string) ($data['firstname'] ?? 'Unknown'),
            (string) ($data['lastname'] ?? 'Employee')
        );
    }
}
