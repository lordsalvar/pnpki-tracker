<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use App\Filament\Resources\FormSubmissions\Schemas\FormSubmissionForm;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('view', $ownerRecord);
    }

    public function form(Schema $schema): Schema
    {
        return FormSubmissionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return FormSubmissionsTable::configure($table)
            ->recordTitleAttribute('lastname')
            ->headerActions([
                CreateAction::make()
                    ->url(fn () => FormSubmissionResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn ($record) => FormSubmissionResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
