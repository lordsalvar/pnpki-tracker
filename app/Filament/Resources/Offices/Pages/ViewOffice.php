<?php

namespace App\Filament\Resources\Offices\Pages;

use App\Filament\Resources\Offices\OfficeResource;
use App\Filament\Resources\Offices\Widgets\OfficeOverviewStats;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOffice extends ViewRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OfficeOverviewStats::class,
        ];
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }

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
            EditAction::make(),
        ];
    }
}
