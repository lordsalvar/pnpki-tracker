<?php

namespace App\Filament\Resources\FormSubmissions\Schemas;

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

class FormSubmissionForm
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

                Section::make('Document Attachments')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Select::make('id_combo')
                            ->label('Select ID Combination')
                            ->options([
                                'national_id' => 'PNPKI form & National ID',
                                'birth_cert_umid' => 'PNPKI form, Birth Cert & UMID',
                                'passport_umid' => 'PNPKI form, Passport & UMID',
                                'birth_cert_valid_ids' => 'PNPKI form, Birth Cert & 2 Valid IDs',
                                'passport_valid_ids' => 'PNPKI form, Passport & 2 valid IDs',
                            ])
                            ->required()
                            ->live()
                            ->disabledOn('edit')
                            ->dehydrated(false)
                            ->columnSpan(2),

                        FileUpload::make('upload_pnpki')
                            ->label('PNPKI Form')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('PNPKI'))
                            ->openable()
                            ->downloadable()
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading PNPKI Form...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(2)
                            ->visible(fn (Get $get) => filled($get('id_combo'))),

                        FileUpload::make('upload_national_id')
                            ->label('Philippine National ID')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('NationalID'))
                            ->openable()
                            ->downloadable()
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading National ID...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(2)
                            ->visible(fn (Get $get) => $get('id_combo') === 'national_id'),

                        FileUpload::make('upload_birth_cert')
                            ->label('Birth Certificate')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('BirthCert'))
                            ->openable()
                            ->downloadable()
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading Birth Certificate...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_umid', 'birth_cert_valid_ids'])),

                        FileUpload::make('upload_passport')
                            ->label('Passport (Bio-data page)')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('Passport'))
                            ->openable()
                            ->downloadable()
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading Passport...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['passport_umid', 'passport_valid_ids'])),

                        FileUpload::make('upload_umid')
                            ->label('UMID Card')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('UMID'))
                            ->openable()
                            ->downloadable()
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading UMID...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_umid', 'passport_umid'])),

                        FileUpload::make('upload_id1')
                            ->label('Valid ID #1')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('ID1'))
                            ->openable()
                            ->downloadable()
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading Valid ID #1...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_valid_ids', 'passport_valid_ids'])),

                        FileUpload::make('upload_id2')
                            ->label('Valid ID #2')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing(self::fileName('ID2'))
                            ->openable()
                            ->downloadable()
                            ->deletable(false)
                            ->previewable()
                            ->uploadingMessage('Uploading Valid ID #2...')
                            ->dehydrated(false)
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => in_array($get('id_combo'), ['birth_cert_valid_ids', 'passport_valid_ids'])),
                    ]),
            ]);
    }

    protected static function fileName(string $type): \Closure
    {
        return function (Get $get, $file) use ($type) {
            $officeId = $get('office_id');
            $officeFolder = 'unknown-office';

            if ($officeId) {
                $office = \App\Models\Office::find($officeId);
                $officeFolder = $office ? str($office->acronym ?? $office->name)->slug() : 'unknown-office';
            }

            $firstname = str($get('firstname') ?? 'Unknown')->slug();
            $lastname = str($get('lastname') ?? 'Submission')->slug();
            $submissionFolder = "{$lastname}-{$firstname}";

            $extension = $file->getClientOriginalExtension();
            $filename = str($lastname)->upper()."_{$type}.{$extension}";

            // Note: this path intentionally omits the record ID prefix.
            // The file is staged here during upload, then moved to its canonical
            // ID-prefixed path (e.g. {id}_{lastname}-{firstname}) by syncAttachments after save.
            return "{$officeFolder}/FormSubmissions/{$submissionFolder}/{$filename}";
        };
    }
}
