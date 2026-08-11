<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Filament\Resources\FormSubmissions\Widgets\FormSubmissionListStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListFormSubmissions extends ListRecords
{
    protected static string $resource = FormSubmissionResource::class;

    public static function getNavigationSort(): ?int
    {
        return static::getResource()::getNavigationSort();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FormSubmissionListStats::class,
        ];
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'submissions' => Tab::make('Form Submissions')
                ->icon('heroicon-o-user-group')
                ->badge($this->scopedSubmissionCount()),
        ];

        if (Auth::user()?->role === UserRole::REPRESENTATIVE->value) {
            $tabs['pending'] = Tab::make('Pending')
                ->icon('heroicon-o-clock')
                ->badge($this->scopedSubmissionCount(FormSubmissionStatus::PENDING))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', FormSubmissionStatus::PENDING->value)
                );
        }

        return [
            ...$tabs,
            'finalized' => Tab::make('Finalized')
                ->icon('heroicon-o-check-circle')
                ->badge($this->scopedSubmissionCount(FormSubmissionStatus::FINALIZED))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', FormSubmissionStatus::FINALIZED->value)
                ),
            'needs_revision' => Tab::make('Needs Revision')
                ->icon('heroicon-o-arrow-uturn-left')
                ->badge($this->scopedSubmissionCount(FormSubmissionStatus::NEEDS_REVISION))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', FormSubmissionStatus::NEEDS_REVISION->value)
                ),
            'for_submission' => Tab::make('For Submission')
                ->icon('heroicon-o-paper-airplane')
                ->badge($this->scopedSubmissionCount(FormSubmissionStatus::FOR_SUBMISSION))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', FormSubmissionStatus::FOR_SUBMISSION->value)
                ),
            'approved_submission' => Tab::make('Approved Submission')
                ->icon('heroicon-o-check-badge')
                ->badge($this->scopedSubmissionCount(FormSubmissionStatus::APPROVED_SUBMISSION))
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', FormSubmissionStatus::APPROVED_SUBMISSION->value)
                ),
        ];
    }

    private function scopedSubmissionCount(?FormSubmissionStatus $status = null): int
    {
        $query = FormSubmissionResource::getEloquentQuery();

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query->count();
    }
}
