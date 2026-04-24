<?php

namespace App\Filament\Resources\ApprovedSubmissions\Pages;

use App\Actions\Batch\DownloadBatchAttachmentsAction;
use App\Filament\Resources\ApprovedSubmissions\ApprovedSubmissionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewApprovedSubmission extends ViewRecord
{
    protected static string $resource = ApprovedSubmissionResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->batch_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('batch.export-csv', $this->record->id))
                ->openUrlInNewTab(),
            Action::make('download_attachments')
                ->label('Download Attachments')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(fn () => app(DownloadBatchAttachmentsAction::class)->execute($this->record)),
        ];
    }
}
