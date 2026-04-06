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
        $pending = $this->scopedQuery()->where('status', FormSubmissionStatus::PENDING->value)->count();
        $finalized = $this->scopedQuery()->where('status', FormSubmissionStatus::FINALIZED->value)->count();
        $needsRevision = $this->scopedQuery()->where('status', FormSubmissionStatus::NEEDS_REVISION->value)->count();

        return [
            Stat::make('Pending', number_format($pending))
                ->description('Awaiting review')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning'),

            Stat::make('Finalized', number_format($finalized))
                ->description('Completed')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),

            Stat::make('Needs revision', number_format($needsRevision))
                ->description('Action required')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger'),
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
}
