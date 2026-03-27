<?php

namespace App\Filament\Resources\Offices\Pages;

use App\Filament\Resources\Offices\OfficeResource;
use Filament\Resources\Pages\EditRecord;

class EditOffice extends EditRecord
{
    protected static string $resource = OfficeResource::class;

    public function getTitle(): string
    {
        return $this->record->acronym;
    }

    public function getSubheading(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),

        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
