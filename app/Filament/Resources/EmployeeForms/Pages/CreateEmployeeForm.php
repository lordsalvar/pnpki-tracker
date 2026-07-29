<?php

namespace App\Filament\Resources\EmployeeForms\Pages;

use App\Filament\Resources\EmployeeForms\EmployeeFormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeForm extends CreateRecord
{
    protected static string $resource = EmployeeFormResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
