<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Services\PsgcService;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\Action;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
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
                    ->required()
                    ->maxLength(255)
                    ->suffixAction(
                        Action::make('set_na')
                            ->label('N/A')
                            ->link() 
                            ->tooltip('Click to set Middle Name as N/A')
                            ->color('gray')
                            ->action(fn (Set $set) => $set('middlename', 'N/A'))
                    ),
                TextInput::make('suffix')
                    ->label('Suffix')
                    ->placeholder('Jr., Sr., III')
                    ->required()
                    ->maxLength(20)
                    ->suffixAction(
                        Action::make('set_na')
                            ->label('N/A')
                            ->link() 
                            ->tooltip('Click to set Suffix as N/A')
                            ->color('gray') 
                            ->action(fn (Set $set) => $set('suffix', 'N/A'))
                    ),
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
                    
                    
                    Group::make()
                    ->relationship('address')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('house_no')
                        ->label('House No.')
                        ->required()
                        ->maxLength(255),
                        TextInput::make('street')
                        ->label('Street')
                        ->required()
                            ->maxLength(255),
                        Select::make('province')
                            ->label('Province')
                            ->options(fn () => app(PsgcService::class)->provinces())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('municipality', null);
                                $set('barangay', null);
                            })
                            ->required(),

                        Select::make('municipality')
                            ->label('City / Municipality')
                            ->options(function (Get $get) {
                                $province = $get('province');
                                if (!$province) return [];
                                return app(PsgcService::class)->municipalities($province);
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('barangay', null);
                            })
                            ->disabled(fn (Get $get) => !$get('province'))
                            ->required(),

                        Select::make('barangay')
                            ->label('Barangay')
                            ->options(function (Get $get) {
                                $municipality = $get('municipality');
                                if (!$municipality) return [];
                                return app(PsgcService::class)->barangays($municipality);
                            })
                            ->searchable()
                            ->live()
                            ->disabled(fn (Get $get) => !$get('municipality'))
                            ->required(),

                        TextInput::make('zip_code')
                            ->label('ZIP Code')
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
                            ->required(),
                    ]),
                Select::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->acronym)
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
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
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