<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Address;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $address = $this->record->address;

        if ($address !== null) {
            $data['address'] = [
                'house_no' => $address->house_no,
                'street' => $address->street,
                'barangay' => $address->barangay,
                'municipality' => $address->municipality,
                'province' => $address->province,
                'zip_code' => $address->zip_code,
            ];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $addressData = $data['address'] ?? [];

        if ($addressData !== []) {
            $address = $this->record->address;

            if ($address === null) {
                $address = Address::query()->create($addressData);
                $data['address_id'] = $address->id;
            } else {
                $address->update($addressData);
            }
        }

        unset($data['address']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
