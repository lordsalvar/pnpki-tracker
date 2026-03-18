<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFormSubmission extends EditRecord
{
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
}
