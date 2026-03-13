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
        'national_id'   => ['upload_pnpki', 'upload_national_id'],
        'passport_umid' => ['upload_pnpki', 'upload_passport', 'upload_umid'],
        'valid_ids'     => ['upload_pnpki', 'upload_id1', 'upload_id2'],
        default         => [],
    };

    foreach ($fileKeys as $fieldName) {
        $fieldValue = $data[$fieldName] ?? [];
        if (empty($fieldValue)) continue;

        $filePath = array_values($fieldValue)[0];

       

        Attachment::create([
            'employee_id' => $employee->id,
            'file_type'   => $fieldName,
            'file_name'   => basename($filePath),
            'file_path'   => $filePath,
            
        ]);
    }

    
}
}