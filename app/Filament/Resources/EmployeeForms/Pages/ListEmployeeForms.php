<?php

namespace App\Filament\Resources\EmployeeForms\Pages;

use App\Filament\Resources\EmployeeForms\EmployeeFormResource;
use App\Models\EmployeeForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeForms extends ListRecords
{
    protected static string $resource = EmployeeFormResource::class;

    public static function getNavigationSort(): ?int
    {
        return static::getResource()::getNavigationSort();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Shareable Form')
                ->disabled(fn (): bool => $this->hasActiveShareableFormForOffice())
                ->tooltip(fn (): ?string => $this->hasActiveShareableFormForOffice()
                    ? 'Only one active shareable form is allowed per office.'
                    : null),
        ];
    }

    private function hasActiveShareableFormForOffice(): bool
    {
        $officeId = auth()->user()?->office_id;

        if (blank($officeId)) {
            return false;
        }

        return EmployeeForm::query()
            ->where('office_id', $officeId)
            ->where('is_active', true)
            ->exists();
    }
}
