<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\BatchResource;
use App\Models\Batch;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBatches extends ListRecords
{
    protected static string $resource = BatchResource::class;

    public function mount(): void
    {
        parent::mount();

        session()->forget([
            'filament.last_viewed_batch_id',
            'filament.batches_nav_last_batch_id',
            'filament.batches_nav_clicks',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        if (Auth::user()?->role !== UserRole::ADMIN->value) {
            return [];
        }

        return [
            'batches' => Tab::make('Batches')
                ->icon('heroicon-o-folder-open')
                ->badge(Batch::query()->count()),
            'pending' => Tab::make('Pending')
                ->icon('heroicon-o-clock')
                ->badge(
                    Batch::query()
                        ->where('application_status', ApplicationStatus::PENDING_FOR_REVIEW->value)
                        ->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('application_status', ApplicationStatus::PENDING_FOR_REVIEW->value)
                ),
            'needs_revision' => Tab::make('Needs Revision')
                ->icon('heroicon-o-arrow-uturn-left')
                ->badge(
                    Batch::query()
                        ->where('application_status', ApplicationStatus::NEEDS_REVISION->value)
                        ->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('application_status', ApplicationStatus::NEEDS_REVISION->value)
                ),
            'for_submission' => Tab::make('For Submission')
                ->icon('heroicon-o-paper-airplane')
                ->badge(
                    Batch::query()
                        ->where('application_status', ApplicationStatus::FOR_SUBMISSION->value)
                        ->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('application_status', ApplicationStatus::FOR_SUBMISSION->value)
                ),
            'approved_submissions' => Tab::make('Approved Submissions')
                ->icon('heroicon-o-check-badge')
                ->badge(
                    Batch::query()
                        ->where('application_status', ApplicationStatus::APPROVED_SUBMISSION->value)
                        ->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('application_status', ApplicationStatus::APPROVED_SUBMISSION->value)
                ),
        ];
    }
}
