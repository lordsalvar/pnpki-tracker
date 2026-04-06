<?php

namespace App\Filament\Resources\FormSubmissions\Widgets;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\FormSubmission;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class FormSubmissionListStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $counts = $this->aggregatedCounts();

        return [
            Stat::make('Pending', number_format($counts['pending']))
                ->description('Awaiting review')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning'),

            Stat::make('Finalized', number_format($counts['finalized']))
                ->description('Completed')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),

            Stat::make('Needs revision', number_format($counts['needs_revision']))
                ->description('Action required')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger'),

            Stat::make('Unassigned', number_format($counts['unassigned']))
                ->description('No batch yet')
                ->descriptionIcon(Heroicon::OutlinedRectangleStack)
                ->color('gray'),
        ];
    }

    private function scopedQuery(): Builder
    {
        $query = FormSubmission::query();

        $user = auth()->user();

        if ($user?->role === UserRole::REPRESENTATIVE->value) {
            $query->where('office_id', $user->office_id);
        }

        return $query;
    }

    /**
     * One round-trip: conditional aggregates over the scoped submission set.
     *
     * @return array{pending: int, finalized: int, needs_revision: int, unassigned: int}
     */
    private function aggregatedCounts(): array
    {
        $pending = FormSubmissionStatus::PENDING->value;
        $finalized = FormSubmissionStatus::FINALIZED->value;
        $needsRevision = FormSubmissionStatus::NEEDS_REVISION->value;

        $row = $this->scopedQuery()
            ->clone()
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count, '.
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as finalized_count, '.
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as needs_revision_count, '.
                'SUM(CASE WHEN batch_id IS NULL THEN 1 ELSE 0 END) as unassigned_count',
                [$pending, $finalized, $needsRevision]
            )
            ->first();

        return [
            'pending' => (int) ($row->pending_count ?? 0),
            'finalized' => (int) ($row->finalized_count ?? 0),
            'needs_revision' => (int) ($row->needs_revision_count ?? 0),
            'unassigned' => (int) ($row->unassigned_count ?? 0),
        ];
    }
}
