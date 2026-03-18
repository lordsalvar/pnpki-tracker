<?php

namespace App\Filament\Resources\EmployeeForms\Pages;

use App\Filament\Resources\EmployeeForms\EmployeeFormResource;
use App\Models\EmployeeForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeForm extends EditRecord
{
    protected static string $resource = EmployeeFormResource::class;

    protected function afterSave(): void
    {
        if ($this->record->is_active) {
            EmployeeForm::query()
                ->where('office_id', $this->record->office_id)
                ->where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
