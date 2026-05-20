<?php

namespace App\Filament\Resources\FormSubmissions\Concerns;

use App\Actions\Batch\AssignBatchAction;
use App\Actions\Batch\UnAssignBatchAction;
use App\Enums\BatchStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Models\Batch;
use App\Models\FormSubmission;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait HasBatchAssignmentActions
{
    protected function makeAssignBatchAction(): Action
    {
        return Action::make('assign_batch')
            ->label(fn (): string => $this->record->batch_id === null ? 'Assign to Batch' : 'Reassign to Batch')
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('info')
            ->visible(fn (): bool => Gate::allows('assignBatch', $this->record))
            ->modalSubmitActionLabel(fn (): string => $this->record->batch_id === null ? 'Assign' : 'Reassign')
            ->form([
                Select::make('batch_id')
                    ->label('Batch')
                    ->options(fn (): array => $this->getBatchOptionsForAssignment())
                    ->required()
                    ->searchable()
                    ->default(fn (): ?string => $this->record->batch_id),
            ])
            ->action(function (array $data): void {
                Gate::authorize('assignBatch', $this->record);

                $batch = Batch::findOrFail($data['batch_id']);

                if ($batch->office_id !== $this->record->office_id) {
                    Notification::make()
                        ->title('The selected batch belongs to a different office.')
                        ->danger()
                        ->send();

                    return;
                }

                if (Auth::user()?->role !== UserRole::ADMIN->value && $batch->status === BatchStatus::FINALIZED) {
                    Notification::make()
                        ->title('Cannot assign to a finalized batch.')
                        ->danger()
                        ->send();

                    return;
                }

                $isReassign = $this->record->batch_id !== null;

                app(AssignBatchAction::class)->execute($this->record, $batch);

                $this->record->refresh();

                Notification::make()
                    ->title($isReassign ? 'Batch reassigned.' : 'Batch assigned.')
                    ->success()
                    ->send();

                if (Auth::user()?->role === UserRole::REPRESENTATIVE->value) {
                    $this->redirect(FormSubmissionResource::getUrl('index'), navigate: true);

                    return;
                }

                if (Auth::user()?->role === UserRole::ADMIN->value && $this->record->batch_id !== null) {
                    $this->redirect(BatchResource::getUrl('view', ['record' => $this->record->batch_id]), navigate: true);

                    return;
                }

                if (method_exists($this, 'refreshFormWithPersistedState')) {
                    $this->refreshFormWithPersistedState();
                }
            });
    }

    protected function makeUnassignBatchAction(): Action
    {
        return Action::make('unassign_batch')
            ->label('Remove from Batch')
            ->icon('heroicon-o-archive-box-x-mark')
            ->color('danger')
            ->visible(fn (): bool => Gate::allows('unassignBatch', $this->record))
            ->requiresConfirmation()
            ->action(function (): void {
                Gate::authorize('unassignBatch', $this->record);

                app(UnAssignBatchAction::class)->execute($this->record);

                $this->record->refresh();

                Notification::make()
                    ->title('Batch unassigned.')
                    ->success()
                    ->send();

                if (method_exists($this, 'refreshFormWithPersistedState')) {
                    $this->refreshFormWithPersistedState();
                }
            });
    }

    /**
     * @return array<string, string>
     */
    protected function getBatchOptionsForAssignment(): array
    {
        /** @var FormSubmission $submission */
        $submission = $this->record;

        $query = Batch::query()
            ->where('office_id', $submission->office_id)
            ->orderBy('batch_name');

        if (Auth::user()?->role !== UserRole::ADMIN->value) {
            $query->where('status', '!=', BatchStatus::FINALIZED);
        }

        return $query->pluck('batch_name', 'id')->all();
    }
}
