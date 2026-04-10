<?php

namespace App\Filament\Resources\FormSubmissions\Schemas;

use App\Enums\CivilStatus;
use App\Enums\Sex;
use App\Services\PsgcService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FormSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Flag remarks')
                    ->description('Remarks from the reviewer.')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->columns(1)
                    ->schema([
                        Placeholder::make('flag_remarks')
                            ->label('Remarks')
                            ->content(fn ($livewire) => $livewire->record?->flag_remarks),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($livewire) => $livewire->record?->flagged_by !== null),
                Section::make('Submission details')
                    ->description('System reference and link to the public registration form, when applicable.')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->columns(2)
                    ->schema([
                        TextInput::make('reference_number')
                            ->label('Reference number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Assigned when the record is saved')
                            ->visible(fn ($livewire) => ! $livewire instanceof CreateRecord),
                        Select::make('form_id')
                            ->label('Source employee form')
                            ->relationship(
                                'employeeForm',
                                'name',
                                fn ($query) => $query->orderBy('name')
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('None — manual entry')
                            ->helperText('Applicants who used a published employee registration link are tied to that form.'),
                    ])
                    ->columnSpanFull(),

                Section::make('Personal information')
                    ->description('Legal name, sex, and contact details for this applicant.')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns(2)
                    ->schema([
                        TextInput::make('firstname')
                            ->label('First Name')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->maxLength(255),
                        TextInput::make('lastname')
                            ->label('Last Name')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->maxLength(255),
                        TextInput::make('middlename')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('set_na')
                                    ->label('N/A')
                                    ->link()
                                    ->tooltip('Click to set Middle Name as N/A')
                                    ->color('gray')
                                    ->action(fn (Set $set) => $set('middlename', 'N/A'))
                                    ->visible(fn ($livewire) => ! $livewire instanceof \App\Filament\Resources\FormSubmissions\Pages\ViewFormSubmission)
                            ),
                        TextInput::make('suffix')
                            ->label('Suffix')
                            ->placeholder('Jr., Sr., III')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->maxLength(20)
                            ->suffixAction(
                                Action::make('set_na')
                                    ->label('N/A')
                                    ->link()
                                    ->tooltip('Click to set Suffix as N/A')
                                    ->color('gray')
                                    ->action(fn (Set $set) => $set('suffix', 'N/A'))
                                    ->visible(fn ($livewire) => ! $livewire instanceof \App\Filament\Resources\FormSubmissions\Pages\ViewFormSubmission)
                            ),
                        Select::make('sex')
                            ->label('Sex')
                            ->options(Sex::class)
                            ->required(),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rule(self::noEmojiRule())
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->helperText('Use your active personal phone number.')
                            ->tel()
                            ->placeholder('e.g. 09171234567')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->default('09')
                            ->minLength(11)
                            ->maxLength(11)
                            ->rule('regex:/^09\d{9}$/')
                            ->validationMessages([
                                'regex' => 'The phone number must start with 09 and be followed by 9 digits (total 11 digits).',
                            ])
                            ->extraInputAttributes([
                                'inputmode' => 'numeric',
                                'oninput' => "let value = this.value.replace(/\\D/g, ''); if (value.startsWith('09')) { value = '09' + value.slice(2); } else if (value.startsWith('9')) { value = '09' + value.slice(1).replace(/^0+/, ''); } else { value = '09' + value.replace(/^0+/, ''); } this.value = value.slice(0, 11);",
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Birth and civil status')
                    ->description('Collected from the public registration form. Complete for manual entries where you have this information.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->columns(2)
                    ->schema([
                        TextInput::make('maiden_name')
                            ->label('Maiden name')
                            ->maxLength(255)
                            ->nullable(),
                        Select::make('civil_status')
                            ->label('Civil status')
                            ->options(CivilStatus::class)
                            ->required()
                            ->native(false),
                        DatePicker::make('birth_date')
                            ->label('Date of birth')
                            ->native(false)
                            ->displayFormat('M d, Y')
                            ->maxDate(now())
                            ->minDate(now()->subYears(100)),
                        TextInput::make('birth_place_country')
                            ->label('Country of birth')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('birth_place_province')
                            ->label('Province / state of birth')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Residential address')
                    ->description('Select province, city or municipality, and barangay in order. Then enter street-level details.')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->columns(2)
                    ->schema([
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

                        TextInput::make('zip_code')
                            ->label('ZIP Code')
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
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

                        TextInput::make('house_no')
                            ->label('House No.')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->maxLength(255),
                        TextInput::make('street')
                            ->label('Street')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),

                Section::make('Employment and tax')
                    ->description('Office assignment, organization, and taxpayer identification.')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->columns(2)
                    ->schema([
                        Select::make('office_id')
                            ->label('Office')
                            ->relationship('office', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->acronym)
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('organization')
                            ->label('Organization')
                            ->maxLength(255),

                        TextInput::make('organizational_unit')
                            ->label('Organizational Unit')
                            ->required()
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->maxLength(255),

                        TextInput::make('tin_number')
                            ->label('TIN Number')
                            ->required()
                            ->length(9)
                            ->rule(self::noSymbolRule())
                            ->rule(self::noEmojiRule())
                            ->mask('999999999')
                            ->unique(ignoreRecord: true)
                            ->rule(self::noEmojiRule())
                            ->rule(self::noSymbolRule())
                            ->validationMessages([
                                'regex' => 'The TIN number must be 9 digits (total 9 digits).',
                            ])
                    ])
                    ->columnSpanFull(),

                Section::make('Document attachments')
                    ->description('ID combination is fixed after submission. Replace PDFs as needed; each file must be PDF, max 5 MB.')
                    ->icon(Heroicon::OutlinedDocumentArrowUp)
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
                    ])
                    ->columnSpanFull(),

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

    protected static function noEmojiRule(): string
    {
        return 'not_regex:/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{200D}\x{FE0F}]/u';
    }

    protected static function noSymbolRule(): string
    {
        return 'regex:/^[\pL\pN\s.,\/-]+$/u';
    }
}
