<?php

namespace App\Filament\Resources\EmployeeForms\Pages;

use App\Filament\Resources\EmployeeForms\EmployeeFormResource;
use App\Models\EmployeeForm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateEmployeeForm extends CreateRecord
{
    protected static string $resource = EmployeeFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['office_id'] = auth()->user()->office_id;
        $data['public_id'] = Str::uuid()->toString();
        $data['name'] = auth()->user()->office->name;

        return $data;
    }

    protected function afterCreate(): void
    {
        EmployeeForm::query()
            ->where('office_id', $this->record->office_id)
            ->where('id', '!=', $this->record->id)
            ->update(['is_active' => false]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
