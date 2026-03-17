<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\Gender;
use App\Services\PsgcService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

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
                                if (! $province) {
                                    return [];
                                }

                                return app(PsgcService::class)->municipalities($province);
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('barangay', null);
                            })
                            ->disabled(fn (Get $get) => ! $get('province'))
                            ->required(),

                        Select::make('barangay')
                            ->label('Barangay')
                            ->options(function (Get $get) {
                                $municipality = $get('municipality');
                                if (! $municipality) {
                                    return [];
                                }

                                return app(PsgcService::class)->barangays($municipality);
                            })
                            ->searchable()
                            ->live()
                            ->disabled(fn (Get $get) => ! $get('municipality'))
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
                                'national_id' => 'PNPKI Form + National ID',
                                'passport_umid' => 'PNPKI Form + Passport + UMID',
                                'valid_ids' => 'PNPKI Form + 2 Valid IDs',
                            ])
                            ->required()
                            ->live()
                            ->dehydrated(false)
                            ->columnSpan(2)
                            ->afterStateUpdated(function (Set $set) {
                                $set('upload_pnpki', null);
                                $set('upload_national_id', null);
                                $set('upload_passport', null);
                                $set('upload_umid', null);
                                $set('upload_id1', null);
                                $set('upload_id2', null);
                            }),

                        // ── PNPKI Form — shown for every branch ──────────────

                        FileUpload::make('upload_pnpki')
                            ->label('PNPKI Form')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('PNPKI'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading PNPKI Form...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(2)
                            ->visible(fn (Get $get) => filled($get('id_combo'))),

                        // ── Branch A: National ID ────────────────────────────

                        FileUpload::make('upload_national_id')
                            ->label('Philippine National ID')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('NationalID'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading National ID...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(2)
                            ->visible(fn (Get $get) => $get('id_combo') === 'national_id'),

                        // ── Branch B: Passport ───────────────────────────────

                        FileUpload::make('upload_passport')
                            ->label('Passport (Bio-data page)')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('Passport'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading Passport...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'passport_umid'),

                        // ── Branch B: UMID ───────────────────────────────────

                        FileUpload::make('upload_umid')
                            ->label('UMID Card')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('UMID'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading UMID...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'passport_umid'),

                        // ── Branch C: Valid ID #1 ────────────────────────────

                        FileUpload::make('upload_id1')
                            ->label('Valid ID #1')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('ID1'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading Valid ID #1...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'valid_ids'),

                        // ── Branch C: Valid ID #2 ────────────────────────────
                        // → NORTHRUP_ID2.pdf  (later: NORTHRUP_ID2_DriversLicense.pdf)
                        FileUpload::make('upload_id2')
                            ->label('Valid ID #2')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('ID2'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading Valid ID #2...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'valid_ids'),
                    ]),
            ]);
    }

    protected static function fileName(string $type): \Closure
    {
        return function (Get $get, $file) use ($type) {
            // 1. Get Office Folder Name
            $officeId = $get('office_id');
            $officeFolder = 'Unknown-Office';

            if ($officeId) {
                $office = \App\Models\Office::find($officeId);
                // We use the acronym or name slugified for the folder
                $officeFolder = $office ? str($office->acronym ?? $office->name)->slug() : 'Unknown-Office';
            }

            // 2. Get Employee Folder Name
            $firstname = str($get('firstname') ?? 'Unknown')->slug();
            $lastname = str($get('lastname') ?? 'Employee')->slug();
            $employeeFolder = "{$lastname}-{$firstname}";

            // 3. File details
            $extension = $file->getClientOriginalExtension();
            $filename = str($lastname)->upper()."_{$type}.{$extension}";

            // Structure: OfficeName/Employees/EmployeeName/Filename
            return "{$officeFolder}/Employees/{$employeeFolder}/{$filename}";
        };
    }
}
