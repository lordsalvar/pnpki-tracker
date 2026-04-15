<?php

namespace App\Filament\Resources\Offices\Widgets;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Models\FormSubmission;
use App\Models\Office;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OfficeOverviewStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = null;

    protected int|string|array $columnSpan = 'full';

    public ?Office $record = null;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        if (! $this->record instanceof Office) {
            return [];
        }

        $employeesCount = (int) ($this->record->number_of_employees ?? 0);
        $submissionsCount = FormSubmission::query()
            ->where('office_id', $this->record->getKey())
            ->count();

        $batchStats = $this->aggregatedBatchCounts();

        return [
            Stat::make('Employees', number_format($employeesCount))
                ->description('Headcount recorded for this office')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('info'),

            Stat::make('Form submissions', number_format($submissionsCount))
                ->description('Submissions linked to this office')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->color('primary'),

            Stat::make('Batches', number_format($batchStats['total']))
                ->description(sprintf(
                    '%s pending · %s finalized · %s needs revision',
                    number_format($batchStats['pending']),
                    number_format($batchStats['finalized']),
                    number_format($batchStats['needs_revision']),
                ))
                ->descriptionIcon(Heroicon::OutlinedRectangleStack)
                ->color('success'),
        ];
    }

    /**
     * @return array{total: int, pending: int, finalized: int, needs_revision: int}
     */
    private function aggregatedBatchCounts(): array
    {
        $pending = BatchStatus::PENDING->value;
        $finalized = BatchStatus::FINALIZED->value;
        $needsRevision = BatchStatus::NEEDS_REVISION->value;

        $row = Batch::query()
            ->where('office_id', $this->record->getKey())
            ->selectRaw(
                'count(*) as total_count, '.
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count, '.
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as finalized_count, '.
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as needs_revision_count',
                [$pending, $finalized, $needsRevision]
            )
            ->first();

        return [
            'total' => (int) ($row->total_count ?? 0),
            'pending' => (int) ($row->pending_count ?? 0),
            'finalized' => (int) ($row->finalized_count ?? 0),
            'needs_revision' => (int) ($row->needs_revision_count ?? 0),
        ];
    }
}
