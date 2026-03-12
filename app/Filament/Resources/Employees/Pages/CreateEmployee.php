<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Address;
use App\Models\Attachment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create address record
        $address = Address::create([
            'house_no'     => $data['house_no'],
            'street'       => $data['street'],
            'barangay'     => $data['barangay'],
            'municipality' => $data['municipality'],
            'province'     => $data['province'],
            'zip_code'     => (string) $data['zip_code'],
        ]);

        $data['address_id'] = $address->id;

        // Remove address fields from employee data
        unset($data['house_no'], $data['street'], $data['barangay'], 
              $data['municipality'], $data['province'], $data['zip_code']);

        return $data;
    }

    protected function afterCreate(): void
{
    $data = $this->form->getRawState();
    $employee = $this->getRecord();
    $combo = $data['id_combo'];

    $fileKeys = match($combo) {
        'national_id'   => ['pnpki_form', 'national_id'],
        'passport_umid' => ['pnpki_form', 'passport', 'umid'],
        'valid_ids'     => ['pnpki_form', 'valid_id_1', 'valid_id_2'],
        default         => [],
    };

    foreach ($fileKeys as $fileKey) {
        if (empty($data[$fileKey])) continue;

        $tempPath = is_array($data[$fileKey])
            ? array_values($data[$fileKey])[0]
            : $data[$fileKey];

        $finalPath = "attachments/employees/{$employee->id}/{$fileKey}.pdf";

        Storage::move($tempPath, $finalPath);

        Attachment::create([
            'employee_id' => $employee->id,
            'file_type'   => $fileKey,
            'file_name'   => basename($tempPath),
            'file_path'   => $finalPath,
            'status'      => 'pending',
        ]);
    }
}
}