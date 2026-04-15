<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBatches extends ListRecords
{
    protected static string $resource = BatchResource::class;

    public function mount(): void
    {
        parent::mount();

        session()->forget([
            'filament.last_viewed_batch_id',
            'filament.batches_nav_last_batch_id',
            'filament.batches_nav_clicks',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
