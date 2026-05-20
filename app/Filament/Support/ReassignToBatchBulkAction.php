<?php

namespace App\Filament\Support;

use App\Actions\Batch\AssignBatchAction;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\FormSubmission;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ReassignToBatchBulkAction
{
    public static function make(string $officeId, ?string $excludeBatchId = null): BulkAction
    {
        return BulkAction::make('reassign_to_batch')
            ->label('Reassign to Batch')
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('info')
            ->visible(fn (): bool => Auth::user()?->role === UserRole::ADMIN->value)
            ->modalSubmitActionLabel('Reassign')
            ->form([
                Select::make('batch_id')
                    ->label('Batch')
                    ->options(fn (): array => static::batchOptions($officeId, $excludeBatchId))
                    ->required()
                    ->searchable(),
            ])
            ->action(function (Collection $records, array $data): void {
                $batch = Batch::findOrFail($data['batch_id']);

                $reassigned = 0;
                $skipped = 0;

                foreach ($records as $submission) {
                    if (! $submission instanceof FormSubmission) {
                        $skipped++;

                        continue;
                    }

                    if (! Gate::allows('assignBatch', $submission)) {
                        $skipped++;

                        continue;
                    }

                    if ($batch->office_id !== $submission->office_id) {
                        $skipped++;

                        continue;
                    }

                    try {
                        app(AssignBatchAction::class)->execute($submission, $batch);
                        $reassigned++;
                    } catch (InvalidArgumentException) {
                        $skipped++;
                    }
                }

                if ($reassigned === 0) {
                    Notification::make()
                        ->title('No submissions could be reassigned.')
                        ->danger()
                        ->send();

                    return;
                }

                $title = "{$reassigned} submission(s) reassigned.";

                if ($skipped > 0) {
                    $title .= " {$skipped} skipped.";
                }

                Notification::make()
                    ->title($title)
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * @return array<string, string>
     */
    protected static function batchOptions(string $officeId, ?string $excludeBatchId): array
    {
        $query = Batch::query()
            ->where('office_id', $officeId)
            ->orderBy('batch_name');

        if ($excludeBatchId !== null) {
            $query->whereKeyNot($excludeBatchId);
        }

        return $query->pluck('batch_name', 'id')->all();
    }
}
