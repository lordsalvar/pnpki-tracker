<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Exports\EmployeeExport;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

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
                    ->url(fn () => EmployeeResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn ($record) => EmployeeResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                // Export CSV for this office's employees only
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $officeId = $this->getOwnerRecord()->id;
                        $officeName = $this->getOwnerRecord()->acronym;

                        $employees = Employee::with(['address', 'office'])
                            ->where('office_id', $officeId)
                            ->get();

                        return Excel::download(
                            new EmployeeExport($employees),
                            "employees-{$officeName}-" . now()->format('Y-m-d') . '.csv',
                            \Maatwebsite\Excel\Excel::CSV
                        );
                    }),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}