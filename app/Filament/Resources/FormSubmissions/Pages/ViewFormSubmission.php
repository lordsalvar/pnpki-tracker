<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Actions\Batch\AssignBatchAction;
use App\Actions\Batch\UnAssignBatchAction;
use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Address;
use App\Models\Batch;
use App\Services\AttachmentRuleService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function getTitle(): string
    {
        $record = $this->record;

        return trim(
            $record->firstname.' '.
            (($record->middlename && $record->middlename !== 'N/A')
                ? strtoupper(substr($record->middlename, 0, 1)).'. '
                : '').
            $record->lastname.
            (($record->suffix && $record->suffix !== 'N/A')
                ? ', '.$record->suffix
                : '')
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('flag_needs_revision')
                ->label('Flag Needs Revision')
                ->icon('heroicon-o-flag')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Flag Needs Revision')
                ->modalDescription('This will mark this submission as Needs Revision.')
                ->visible(fn () => Auth::user()?->role === UserRole::REPRESENTATIVE->value
                    && $this->record->status === FormSubmissionStatus::FINALIZED
                    && $this->record->batch?->status === BatchStatus::FINALIZED)
                ->action(function () {
                    $this->record->update([
                        'status' => FormSubmissionStatus::NEEDS_REVISION->value,
                    ]);

                    Notification::make()
                        ->title('Submission flagged as Needs Revision.')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),
            Action::make('assign_batch')
                ->label('Assign to Batch')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('info')
                ->visible(fn () => $this->record->status === FormSubmissionStatus::FINALIZED && $this->record->batch_id === null)
                ->form([
                    Select::make('batch_id')
                        ->label('Batch')
                        ->options(
                            Batch::query()
                                ->where('office_id', Auth::user()->office_id)
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
            Action::make('unassign_batch')
                ->label('Remove from Batch')
                ->icon('heroicon-o-archive-box-x-mark')
                ->color('danger')
                ->visible(function () {
                    return $this->record->batch_id !== null
                        && $this->record->status !== FormSubmissionStatus::FINALIZED
                        && $this->record->batch?->status !== BatchStatus::FINALIZED;
                })
                ->requiresConfirmation()
                ->action(function () {
                    app(UnAssignBatchAction::class)->execute($this->record);

                    Notification::make()
                        ->title('Batch unassigned.')
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
