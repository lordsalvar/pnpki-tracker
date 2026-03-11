<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                //Full Name
                TextColumn::make('fullname')
                    ->label('Full Name')
                    ->getStateUsing(fn ($record) => trim(
                        $record->firstname . ' ' .
                        (($record->middlename && $record->middlename !== 'N/A')
                            ? strtoupper(substr($record->middlename, 0, 1)) . '. '
                            : '') .
                        $record->lastname .
                        (($record->suffix && $record->suffix !== 'N/A')
                            ? ', ' . $record->suffix
                            : '')
                    ))
                    ->searchable(query: function ($query, string $search) {
                        $query->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('middlename', 'like', "%{$search}%");
                    }),

                //Office
                TextColumn::make('office.acronym')
                    ->label('Office')
                    ->searchable()
                    ->tooltip(fn ($record) => $record->office?->name),

                // Email
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                // Phone
                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),

                    // Hidden by default
                // Gender
                TextColumn::make('gender')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),


                
                TextColumn::make('organizational_unit')
                    ->label('Org. Unit')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('full_address')
                    ->label('Address')
                    ->getStateUsing(fn ($record) => implode(', ', array_filter([
                        $record->address?->house_no,
                        $record->address?->street,
                        $record->address?->barangay,
                        $record->address?->municipality,
                        $record->address?->province,
                        $record->address?->zip_code,
                    ])))
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('address', fn ($q) => $q
                            ->where('street', 'like', "%{$search}%")
                            ->orWhere('barangay', 'like', "%{$search}%")
                            ->orWhere('municipality', 'like', "%{$search}%")
                            ->orWhere('province', 'like', "%{$search}%")
                        );
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tin_number')
                    ->label('TIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                \Filament\Tables\Filters\SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'male'   => 'Male',
                        'female' => 'Female',
                        'other'  => 'Other',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'acronym'),

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