<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use App\Models\Attachment;
use Filament\Resources\Pages\CreateRecord;

class CreateFormSubmission extends CreateRecord
{
    protected static string $resource = FormSubmissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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
        $combo = $data['id_combo'];

        $fileKeys = match ($combo) {
            'national_id' => ['upload_pnpki', 'upload_national_id'],
            'passport_umid' => ['upload_pnpki', 'upload_passport', 'upload_umid'],
            'valid_ids' => ['upload_pnpki', 'upload_id1', 'upload_id2'],
            default => [],
        };

        foreach ($fileKeys as $fieldName) {
            $fieldValue = $data[$fieldName] ?? [];

            if (empty($fieldValue)) {
                continue;
            }

            $filePath = array_values($fieldValue)[0];

            Attachment::create([
                'employee_id' => $formSubmission->id,
                'file_type' => $fieldName,
                'file_name' => basename($filePath),
                'file_path' => $filePath,
            ]);
        }
    }
    
}
