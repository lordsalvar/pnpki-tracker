<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('view', $ownerRecord);
    }

    public function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return EmployeesTable::configure($table)
            ->recordTitleAttribute('lastname')
            ->headerActions([
                CreateAction::make()
                    ->url(fn () => EmployeeResource::getUrl('create')), // updated
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn ($record) => EmployeeResource::getUrl('edit', ['record' => $record])), // updated
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

    }
}
