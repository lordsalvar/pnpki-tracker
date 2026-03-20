<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Filament\Resources\Batches\BatchResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Form;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;


class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $relatedResource = BatchResource::class;

    public function table(Table $table): Table
    {
        $isFinalized = $this->ownerRecord->status === \App\Enums\BatchStatus::FINALIZED->value;

        return FormSubmissionsTable::configure($table)
            ->recordTitleAttribute('lastname')
            ->recordActions(
                $isFinalized ? []: [
                    EditAction::make(),
                ],
            )
            ->toolbarActions(
                $isFinalized ? []: [
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                    ]),
                ],
            );
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === \App\Filament\Resources\Batches\Pages\ViewBatch::class;
    }
}
