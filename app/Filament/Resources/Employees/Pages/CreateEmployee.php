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
        $addressData = $data['address'] ?? [];

        if ($addressData !== []) {
            $address = Address::query()->create($addressData);
            $data['address_id'] = $address->id;
        }

        unset($data['address']);

        return $data;
    }
}
