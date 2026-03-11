<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('firstname')
                    ->label('First Name')
                    ->searchable(),
                TextColumn::make('lastname')
                    ->label('Last Name')
                    ->searchable(),
                TextColumn::make('middlename')
                    ->label('Middle Name')
                    ->searchable(),
                TextColumn::make('suffix')
                    ->label('Suffix')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('Phone Number')
                    ->searchable(),

                    
                    //address
                 // Address columns (from addresses migration)
                TextColumn::make('address.house_no')
                    ->label('House No.')
                    ->searchable(),
                TextColumn::make('address.street')
                    ->label('Street')
                    ->searchable(),
                TextColumn::make('address.barangay')
                    ->label('Barangay')
                    ->searchable(),
                TextColumn::make('address.municipality')
                    ->label('Municipality')
                    ->searchable(),
                TextColumn::make('address.province')
                    ->label('Province')
                    ->searchable(),
                TextColumn::make('address.zip_code')
                    ->label('Zip Code')
                    ->searchable(),


                    
                //offices
                            
                TextColumn::make('office.name')
                    ->label('Office')
                    ->searchable(),
                TextColumn::make('office.acronym')
                    ->label('Acronym')
                    ->searchable(),


                TextColumn::make('organizational_unit')
                    ->label('Organizational Unit')
                    ->searchable(),
                TextColumn::make('gender')
                    ->searchable(),
                TextColumn::make('tin_number')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
