<?php

namespace App\Filament\Resources\EmployeeForms\Pages;

use App\Filament\Resources\EmployeeForms\EmployeeFormResource;
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
                ->label('New Shareable Form'),
        ];
    }
}
