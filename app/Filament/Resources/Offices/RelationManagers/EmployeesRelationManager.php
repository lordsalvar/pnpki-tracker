<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use EmptyIterator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use GuzzleHttp\Promise\Create;

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
