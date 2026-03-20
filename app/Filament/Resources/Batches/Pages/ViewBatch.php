<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use App\Enums\BatchStatus;
use App\Actions\FinalizeBatchAction;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('finalize')
                ->label('Finalize Batch')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Finalize Batch')
                ->modalDescription('Once finalized, this batch can no longer be edited. Are you sure?')
                ->hidden(fn () => $this->record->status === BatchStatus::FINALIZED->value)
                ->action(function () {
                    app(FinalizeBatchAction::class)->execute($this->record);
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
