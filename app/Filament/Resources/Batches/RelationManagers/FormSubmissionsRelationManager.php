<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $relatedResource = BatchResource::class;

    protected static ?string $title = 'Employees';

    public function table(Table $table): Table
    {
        $isFinalized = $this->ownerRecord->status === \App\Enums\BatchStatus::FINALIZED->value;

        return FormSubmissionsTable::configure($table)
            ->recordTitleAttribute('lastname')
            ->recordActions(
                $isFinalized ? [] : [
                    ViewAction::make()
                    ->url(fn ($record) => FormSubmissionResource::getUrl('view', ['record' => $record]))
                    ->visible(fn ($record) => $record->status === \App\Enums\FormSubmissionStatus::FINALIZED),
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
