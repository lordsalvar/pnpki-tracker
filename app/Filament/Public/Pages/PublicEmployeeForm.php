<?php

namespace App\Filament\Public\Pages;

use App\Enums\Gender;
use App\Models\Address;
use App\Models\Attachment;
use App\Models\EmployeeForm;
use App\Models\FormSubmission;
use App\Services\PsgcService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Http;

class PublicEmployeeForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.public.pages.public-employee-form';

    public ?EmployeeForm $formModel = null;

    public ?array $employeeData = [];

    public bool $submitted = false;

    public ?string $captchaToken = null;

    public static function getRoutePath(Panel $panel): string
    {
        return '/forms/{publicId}';
    }

    public function getTitle(): string|Htmlable
    {
        return $this->formModel?->name ?? 'Employee Registration';
    }

    public function mount(string $publicId): void
    {
        $this->formModel = EmployeeForm::where('public_id', $publicId)->firstOrFail();
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
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
                            ->required()
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('set_na_mid')
                                    ->label('N/A')
                                    ->link()
                                    ->color('gray')
                                    ->action(fn (Set $set) => $set('middlename', 'N/A'))
                            ),

                        TextInput::make('suffix')
                            ->label('Suffix')
                            ->placeholder('Jr., Sr., III')
                            ->required()
                            ->maxLength(20)
                            ->suffixAction(
                                Action::make('set_na_suf')
                                    ->label('N/A')
                                    ->link()
                                    ->color('gray')
                                    ->action(fn (Set $set) => $set('suffix', 'N/A'))
                            ),
                    ]),

                Section::make('Contact Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                    ]),

                Section::make('Address')
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
                            ->afterStateUpdated(fn (Set $set) => $set('barangay', null))
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

                Section::make('Employment Details')
                    ->columns(2)
                    ->schema([
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
                            ->maxLength(20),
                    ]),

                Section::make('Document Attachments')
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
                            ->columnSpan(2)
                            ->afterStateUpdated(function (Set $set) {
                                $set('upload_pnpki', null);
                                $set('upload_national_id', null);
                                $set('upload_passport', null);
                                $set('upload_umid', null);
                                $set('upload_id1', null);
                                $set('upload_id2', null);
                            }),

                        FileUpload::make('upload_pnpki')
                            ->label('PNPKI Form')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('PNPKI'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading PNPKI Form...')
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
                            ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('NationalID'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading National ID...')
                            ->required()
                            ->columnSpan(2)
                            ->visible(fn (Get $get) => $get('id_combo') === 'national_id'),

                        FileUpload::make('upload_passport')
                            ->label('Passport (Bio-data page)')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('Passport'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading Passport...')
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'passport_umid'),

                        FileUpload::make('upload_umid')
                            ->label('UMID Card')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('UMID'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading UMID...')
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'passport_umid'),

                        FileUpload::make('upload_id1')
                            ->label('Valid ID #1')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('ID1'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading Valid ID #1...')
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'valid_ids'),

                        FileUpload::make('upload_id2')
                            ->label('Valid ID #2')
                            ->helperText('PDF only · Max 5 MB')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->disk('local')
                            ->directory('attachments')
                            ->visibility('private')
                            ->getUploadedFileNameForStorageUsing($this->fileNameForStorage('ID2'))
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Uploading Valid ID #2...')
                            ->required()
                            ->columnSpan(1)
                            ->visible(fn (Get $get) => $get('id_combo') === 'valid_ids'),
                    ]),
            ])
            ->statePath('employeeData');
    }

    public function submit(): void
    {
        if (! $this->verifyCaptcha()) {
            Notification::make()
                ->title('CAPTCHA verification failed. Please try again.')
                ->danger()
                ->send();

            $this->captchaToken = null;

            return;
        }

        $data = $this->form->getState();

        $rep = $this->formModel->user;

        $address = Address::create([
            'house_no' => $data['house_no'],
            'street' => $data['street'],
            'barangay' => $data['barangay'],
            'municipality' => $data['municipality'],
            'province' => $data['province'],
            'zip_code' => $data['zip_code'],
        ]);

        $formSubmission = FormSubmission::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'middlename' => $data['middlename'],
            'suffix' => $data['suffix'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'organizational_unit' => $data['organizational_unit'],
            'gender' => $data['gender'],
            'tin_number' => $data['tin_number'],
            'address_id' => $address->id,
            'office_id' => $rep->office_id,
            'form_id' => $this->formModel->id,
        ]);

        $this->saveAttachments($formSubmission, $data);

        $this->submitted = true;
        $this->form->fill();
    }

    private function verifyCaptcha(): bool
    {
        if (blank($this->captchaToken)) {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret'),
            'response' => $this->captchaToken,
            'remoteip' => request()->ip(),
        ]);

        return (bool) $response->json('success');
    }

    private function saveAttachments(FormSubmission $formSubmission, array $data): void
    {
        $uploads = [
            'upload_pnpki' => 'PNPKI',
            'upload_national_id' => 'NationalID',
            'upload_passport' => 'Passport',
            'upload_umid' => 'UMID',
            'upload_id1' => 'ID1',
            'upload_id2' => 'ID2',
        ];

        foreach ($uploads as $field => $type) {
            if (! empty($data[$field])) {
                $path = is_array($data[$field]) ? array_values($data[$field])[0] : $data[$field];

                Attachment::create([
                    'form_submission_id' => $formSubmission->id,
                    'file_type' => $type,
                    'file_name' => basename($path),
                    'file_path' => $path,
                ]);
            }
        }
    }

    private function fileNameForStorage(string $type): \Closure
    {
        return function (Get $get, $file) use ($type) {
            $office = $this->formModel?->user?->office;
            $officeFolder = $office
                ? str($office->acronym ?? $office->name)->slug()
                : 'unknown-office';

            $firstname = str($get('firstname') ?? 'Unknown')->slug();
            $lastname = str($get('lastname') ?? 'Employee')->slug();
            $employeeFolder = "{$lastname}-{$firstname}";

            $extension = $file->getClientOriginalExtension();
            $filename = str($lastname)->upper()."_{$type}.{$extension}";

            return "{$officeFolder}/Employees/{$employeeFolder}/{$filename}";
        };
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
