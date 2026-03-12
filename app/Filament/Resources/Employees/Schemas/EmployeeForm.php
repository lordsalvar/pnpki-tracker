<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use App\Services\PsgcService;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\Action;
use App\Enums\Gender;

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
                    ->options(Gender::class)
                    ->required(),

                TextInput::make('tin_number')
                    ->label('TIN Number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                // DOCUMENT UPLOADS
   
                Section::make('Document Attachments')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([

                        Select::make('id_combo')
                            ->label('Select ID Combination')
                            ->options([
                                'national_id'   => 'PNPKI Form + National ID',
                                'passport_umid' => 'PNPKI Form + Passport + UMID',
                                'valid_ids'     => 'PNPKI Form + 2 Valid IDs',
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(2),

                        // PNPKI Form — always required regardless of combo
                        FileUpload::make('pnpki_form')
                            ->label('PNPKI Form')
                            ->directory('attachments/temp')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->visible(fn (Get $get) => filled($get('id_combo')))
                            ->columnSpan(2),

                        // Combo 1 — National ID
                        FileUpload::make('national_id')
                            ->label('National ID')
                            ->directory('attachments/temp')
                            ->acceptedFileTypes(['application/pdf'])
                            ->rules(['mimes:pdf'])
                            ->maxSize(5120)
                            ->required()
                            ->visible(fn (Get $get) => $get('id_combo') === 'national_id')
                            ->columnSpan(2),

                        // Combo 2 — Passport
                        FileUpload::make('passport')
                            ->label('Passport')
                            ->directory('attachments/temp')
                            ->acceptedFileTypes(['application/pdf'])
                            ->rules(['mimes:pdf'])
                            ->maxSize(5120)
                            ->required()
                            ->visible(fn (Get $get) => $get('id_combo') === 'passport_umid')
                            ->columnSpan(1),

                        // Combo 2 — UMID
                        FileUpload::make('umid')
                            ->label('UMID')
                            ->directory('attachments/temp')
                            ->acceptedFileTypes(['application/pdf'])
                            ->rules(['mimes:pdf'])
                            ->maxSize(5120)
                            ->required()
                            ->visible(fn (Get $get) => $get('id_combo') === 'passport_umid')
                            ->columnSpan(1),

                        // Combo 3 — Valid ID 1
                        FileUpload::make('valid_id_1')
                            ->label('Valid ID 1')
                            ->directory('attachments/temp')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->required()
                            ->visible(fn (Get $get) => $get('id_combo') === 'valid_ids')
                            ->columnSpan(1),

                        // Combo 3 — Valid ID 2
                        FileUpload::make('valid_id_2')
                            ->label('Valid ID 2')
                            ->directory('attachments/temp')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->required()
                            ->visible(fn (Get $get) => $get('id_combo') === 'valid_ids')
                            ->columnSpan(1),

                    ]),

            ]);
    }
}