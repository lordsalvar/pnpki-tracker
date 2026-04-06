<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Filament\Resources\FormSubmissions\Widgets\FormSubmissionListStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFormSubmissions extends ListRecords
{
    protected static string $resource = FormSubmissionResource::class;

    public static function getNavigationSort(): ?int
    {
        return static::getResource()::getNavigationSort();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FormSubmissionListStats::class,
        ];
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
