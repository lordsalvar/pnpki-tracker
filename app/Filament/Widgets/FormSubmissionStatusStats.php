<?php

namespace App\Filament\Widgets;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\FormSubmission;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FormSubmissionStatusStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Form status';

    public static function canView(): bool
    {
        return auth()->user()?->role === UserRole::ADMIN->value;
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $counts = $this->aggregatedCounts();

        return [
            Stat::make('Pending', number_format($counts['pending']))
                ->description('Awaiting representative review')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning'),

            Stat::make('For Submission', number_format($counts['for_submission']))
                ->description('Cleared for the DICT package')
                ->descriptionIcon(Heroicon::OutlinedPaperAirplane)
                ->color('success'),

            Stat::make('Approved', number_format($counts['approved_submission']))
                ->description('Approved submissions')
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color('primary'),
        ];
    }

    /**
     * @return array{pending: int, for_submission: int, approved_submission: int}
     */
    private function aggregatedCounts(): array
    {
        $pending = FormSubmissionStatus::PENDING->value;
        $forSubmission = FormSubmissionStatus::FOR_SUBMISSION->value;
        $approved = FormSubmissionStatus::APPROVED_SUBMISSION->value;

        $row = FormSubmission::query()
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count, '.
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as for_submission_count, '.
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_submission_count',
                [$pending, $forSubmission, $approved]
            )
            ->first();

        return [
            'pending' => (int) ($row->pending_count ?? 0),
            'for_submission' => (int) ($row->for_submission_count ?? 0),
            'approved_submission' => (int) ($row->approved_submission_count ?? 0),
        ];
    }
}
