<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Actions\Batch\AssignBatchAction;
use App\Enums\FormSubmissionStatus;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use App\Models\Batch;
use App\Services\AttachmentRuleService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_batch')
                ->label('Assign to Batch')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('info')
                ->visible(fn () => $this->record->status === FormSubmissionStatus::FINALIZED)
                ->form([
                    Select::make('batch_id')
                        ->label('Batch')
                        ->options(
                            Batch::query()
                                ->orderBy('batch_name')
                                ->pluck('batch_name', 'id')
                        )
                        ->required()
                        ->searchable()
                        ->default(fn () => $this->record->batch_id),
                ])
                ->action(function (array $data) {
                    $batch = Batch::findOrFail($data['batch_id']);

                    app(AssignBatchAction::class)->execute($this->record, $batch);

                    Notification::make()
                        ->title('Batch assigned.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $address = Address::find($data['address_id']);

        if ($address) {
            $data['house_no'] = $address->house_no;
            $data['street'] = $address->street;
            $data['barangay'] = $address->barangay;
            $data['municipality'] = $address->municipality;
            $data['province'] = $address->province;
            $data['zip_code'] = $address->zip_code;
        }

        $attachmentPaths = $this->getAttachmentPathsByField();

        foreach ($attachmentPaths as $field => $path) {
            $data[$field] = [$path => $path];
        }

        $data['id_combo'] = $this->detectComboFromAttachmentPaths($attachmentPaths);

        return $data;
    }

    private function getAttachmentPathsByField(): array
    {
        $ruleService = app(AttachmentRuleService::class);
        $paths = [];

        foreach ($this->record->attachments as $attachment) {
            $field = $ruleService->fieldFromFileType((string) $attachment->file_type);

            if ($field === null) {
                continue;
            }

            $paths[$field] = $attachment->file_path;
        }

        return $paths;
    }

    private function detectComboFromAttachmentPaths(array $paths): ?string
    {
        return app(AttachmentRuleService::class)->detectComboFromPaths($paths);
    }
}
