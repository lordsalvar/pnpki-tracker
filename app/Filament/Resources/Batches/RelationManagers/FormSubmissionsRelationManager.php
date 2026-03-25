<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $relatedResource = BatchResource::class;

    protected static ?string $title = 'Employees';

    public function table(Table $table): Table
    {
        $isFinalized = $this->ownerRecord->status === BatchStatus::FINALIZED->value;

        return FormSubmissionsTable::configure($table)
            ->recordTitleAttribute('lastname')
            ->recordActions(
                $isFinalized ? [
                    Action::make('flag_needs_revision')
                        ->label('Flag Needs Revision')
                        ->icon('heroicon-o-flag')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Flag Needs Revision')
                        ->modalDescription('This will mark the selected form submission as Needs Revision.')
                        ->visible(fn ($record) => Auth::user()?->role === UserRole::REPRESENTATIVE->value
                            && $record->status === FormSubmissionStatus::FINALIZED)
                        ->action(function ($record) {
                            $record->update([
                                'status' => FormSubmissionStatus::NEEDS_REVISION->value,
                            ]);

                            Notification::make()
                                ->title('Submission flagged as Needs Revision.')
                                ->success()
                                ->send();
                        }),
                    ViewAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('view', ['record' => $record]))
                        ->visible(fn ($record) => in_array($record->status, [FormSubmissionStatus::FINALIZED, FormSubmissionStatus::NEEDS_REVISION])),
                ] : [
                    EditAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('edit', ['record' => $record]))
                        ->visible(fn ($record) => $this->ownerRecord->status === BatchStatus::NEEDS_REVISION
                            && $record->status === FormSubmissionStatus::NEEDS_REVISION
                            && Gate::allows('update', $record)),
                    ViewAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('view', ['record' => $record]))
                        ->visible(fn ($record) => in_array($record->status, [FormSubmissionStatus::FINALIZED, FormSubmissionStatus::NEEDS_REVISION])),
                ],
            )
            ->toolbarActions(
                $isFinalized ? [] : [
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ],
            );
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === \App\Filament\Resources\Batches\Pages\ViewBatch::class;
    }
}
