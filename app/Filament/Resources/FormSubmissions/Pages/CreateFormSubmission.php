<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Enums\FormSubmissionStatus;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use App\Models\Attachment;
use App\Services\AttachmentRuleService;
use Filament\Resources\Pages\CreateRecord;

class CreateFormSubmission extends CreateRecord
{
    protected static string $resource = FormSubmissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['status'] ?? null)) {
            $data['status'] = FormSubmissionStatus::PENDING->value;
        }

        $address = Address::create([
            'house_no' => $data['house_no'],
            'street' => $data['street'],
            'barangay' => $data['barangay'],
            'municipality' => $data['municipality'],
            'province' => $data['province'],
            'zip_code' => (string) $data['zip_code'],
        ]);

        $data['address_id'] = $address->id;

        unset($data['house_no'], $data['street'], $data['barangay'],
            $data['municipality'], $data['province'], $data['zip_code']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        $formSubmission = $this->getRecord();

        $ruleService = app(AttachmentRuleService::class);
        $fileKeys = $ruleService->activeFieldsForCombo($data['id_combo'] ?? null);

        foreach ($fileKeys as $fieldName) {
            $fieldValue = $data[$fieldName] ?? [];

            if (empty($fieldValue)) {
                continue;
            }

            $filePath = is_array($fieldValue) ? array_values($fieldValue)[0] : $fieldValue;

            $type = $ruleService->fileTypeForField($fieldName);

            if ($type === null) {
                continue;
            }

            Attachment::create([
                'form_submission_id' => $formSubmission->id,
                'file_type' => $type,
                'file_name' => basename((string) $filePath),
                'file_path' => $filePath,
            ]);
        }

        $formSubmission = $this->getRecord();

        $recipients = \App\Models\User::query()
            ->where('role', \App\Enums\UserRole::REPRESENTATIVE->value)
            ->where('office_id', $formSubmission->office_id)
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\NewFormSubmissionNotification($formSubmission));
        }
    }
}
