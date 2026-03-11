<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstname')
                    ->label('First Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('lastname')
                    ->label('Last Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('middlename')
                    ->label('Middle Name')
                    ->maxLength(255),
                TextInput::make('suffix')
                    ->label('Suffix')
                    ->placeholder('e.g. Jr., Sr., III')
                    ->maxLength(20),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                Fieldset::make('Address')
                    ->relationship('address')
                    ->schema([
                        TextInput::make('house_no')
                            ->label('House No.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('street')
                            ->label('Street')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('barangay')
                            ->label('Barangay')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('municipality')
                            ->label('Municipality')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('province')
                            ->label('Province')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('zip_code')
                            ->label('Zip Code')
                            ->required()
                            ->maxLength(10),
                    ]),
                    


                Select::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) =>
                        "{$record->name} ({$record->acronym})"
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('organizational_unit')
                    ->label('Organizational Unit')
                    ->required()
                    ->maxLength(255),
                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'male'   => 'Male',
                        'female' => 'Female',
                        'other'  => 'Other',
                    ])
                    ->required(),
                TextInput::make('tin_number')
                    ->label('TIN Number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
            ]);
    }
}