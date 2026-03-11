<?php

namespace App\Filament\Resources\Offices\RelationManagers;

use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\EmployeeResource;
use EmptyIterator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('lastname')
            ->columns([
                TextColumn::make('firstname')
                    ->label('Full Name')
                    ->formatStateUsing(fn ($record) => $record->lastname . ', ' . $record->firstname . ' ' . $record->middlename)
                    ->searchable(),
                TextColumn::make('office.acronym')
                    ->label('Office')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(fn () => EmployeeResource::getUrl('create')), // updated
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);



    }
}
