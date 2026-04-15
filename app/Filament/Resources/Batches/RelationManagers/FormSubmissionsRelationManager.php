<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Enums\BatchStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $relatedResource = BatchResource::class;

    protected static ?string $title = 'Form Submissions';

    public function table(Table $table): Table
    {
        $isFinalized = $this->ownerRecord->status === BatchStatus::FINALIZED->value;

        return FormSubmissionsTable::configure($table)
            ->recordTitleAttribute('lastname')
            ->recordUrl(function ($record): ?string {
                if (! $record) {
                    return null;
                }

                if ($record->status === FormSubmissionStatus::NEEDS_REVISION) {
                    if (
                        Auth::user()?->role === UserRole::REPRESENTATIVE->value
                        && $this->ownerRecord->status !== BatchStatus::NEEDS_REVISION->value
                    ) {
                        return FormSubmissionResource::getUrl('view', [
                            'record' => $record,
                            'batch' => $this->ownerRecord->getKey(),
                        ]);
                    }

                    return FormSubmissionResource::getUrl('edit', [
                        'record' => $record,
                        'batch' => $this->ownerRecord->getKey(),
                    ]);
                }

                return FormSubmissionResource::getUrl('view', [
                    'record' => $record,
                    'batch' => $this->ownerRecord->getKey(),
                ]);
            })
            ->recordActions(
                $isFinalized ? [
                    ViewAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('view', [
                            'record' => $record,
                            'batch' => $this->ownerRecord->getKey(),
                        ]))
                        ->visible(fn ($record) => in_array($record->status, [FormSubmissionStatus::FINALIZED, FormSubmissionStatus::FOR_SUBMISSION, FormSubmissionStatus::NEEDS_REVISION])),
                ] : [
                    EditAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('edit', [
                            'record' => $record,
                            'batch' => $this->ownerRecord->getKey(),
                        ]))
                        ->visible(fn ($record) => $this->ownerRecord->status === BatchStatus::NEEDS_REVISION
                            && $record->status === FormSubmissionStatus::NEEDS_REVISION
                            && Gate::allows('update', $record)),
                    ViewAction::make()
                        ->url(fn ($record) => FormSubmissionResource::getUrl('view', [
                            'record' => $record,
                            'batch' => $this->ownerRecord->getKey(),
                        ]))
                        ->visible(fn ($record) => in_array($record->status, [FormSubmissionStatus::FINALIZED, FormSubmissionStatus::FOR_SUBMISSION, FormSubmissionStatus::NEEDS_REVISION])),
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
