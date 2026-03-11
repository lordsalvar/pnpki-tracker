<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Address;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $address = Address::create([
            'house_no'     => $data['house_no'],
            'street'       => $data['street'],
            'barangay'     => $data['barangay'],
            'municipality' => $data['municipality'],
            'province'     => $data['province'],
            'zip_code'     => $data['zip_code'],
        ]);

        $data['address_id'] = $address->id;

        unset($data['house_no'], $data['street'], $data['barangay'], $data['municipality'], $data['province'], $data['zip_code']);

        return $data;
    }
}
