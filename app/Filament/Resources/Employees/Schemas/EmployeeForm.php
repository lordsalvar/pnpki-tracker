<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstname')
                    ->required(),
                TextInput::make('lastname')
                    ->required(),
                TextInput::make('middlename'),
                TextInput::make('suffix'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone_number')
                    ->tel()
                    ->required(),
                TextInput::make('address.house_no')
                    ->required(),
                TextInput::make('address.street')
                    ->required(),
                TextInput::make('address.barangay')
                    ->required(),
                TextInput::make('address.municipality')
                    ->required(),
                TextInput::make('address.province')
                    ->required(),
                TextInput::make('address.zip_code')
                    ->required(),
                Select::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('organizational_unit')
                    ->required(),
                TextInput::make('gender')
                    ->required(),
                TextInput::make('tin_number')
                    ->required(),
            ]);
    }
}
